import { useState, useEffect } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { addQueryArgs } from "@wordpress/url";
import { store as noticesStore } from "@wordpress/notices";
import { store as coreStore } from "@wordpress/core-data";
import apiFetch from "@wordpress/api-fetch";
import { select, useDispatch } from "@wordpress/data";
import debounce from "debounce-promise";
import EntitySearchDropdown from "./EntitySearchDropdown";
import VideoIcon from "./VideoIcon";
import { Button, MenuItem } from "@wordpress/components";
import { css, jsx } from "@emotion/core";

const SelectMediaDropdown = ({ onSelect, value, ...props }) => {
  const [search, setSearch] = useState("");
  const [page, setPage] = useState(1);
  const [isLoading, setIsLoading] = useState(false);
  const [videoList, setVideoList] = useState([]);
  const [totalPages, setTotalPages] = useState(0);
  const { createErrorNotice } = useDispatch(noticesStore);
  const { receiveEntityRecords } = useDispatch(coreStore);
  const [dropdownOpen, setDropdownOpen] = useState(false);

  const handleSelection = (video) => {
    if (!video) return;
    onSelect(video);
  };

  // debounce the search.
  const debounceSearch = debounce(
    () => {
      setPage(1); // reset the page.
      setVideoList(null); // clear the videos.
      doFetch() // fetch the videos.
    },
    500,
    {
      leading: true,
    }
  );

  // when the search term changes, do a debounce search.
  useEffect(() => {
    if (!dropdownOpen) return;
    debounceSearch(search);
  }, [search, dropdownOpen]);

  // when the page changes, fetch the videos.
  useEffect(() => {
    if (!dropdownOpen) return;
    doFetch();
  }, [page, dropdownOpen]);

  // check if there are more pages.
  const hasMore = page < totalPages;

  // set the next page.
  const nextPage = () => {
    let newPage = page + 1;
    newPage = newPage > totalPages ? totalPages : newPage;
    setPage(newPage);
  };

  // Fetch videos from the server.
  const doFetch = async () => {
    try {
      setIsLoading(true);

      const baseURL = select(coreStore).getEntityConfig(
        "postType",
        "pp_video_block"
      ).baseURL;

      const res = await apiFetch({
        path: addQueryArgs(baseURL, {
          search,
          page,
          per_page: 5,
          _embed: 1,
        }),
        parse: false,
      });

      const videos = await res.json();

      setTotalPages(parseInt(res.headers.get("X-WP-TotalPages")));
      receiveEntityRecords("postType", "pp_video_block", videos);

      if (!search && page > 1) {
        setVideoList([...videoList, ...videos]);
      } else {
        setVideoList(videos);
      }
    } catch (error) {
      createErrorNotice(
        error?.message || __("Something went wrong", "presto-player"),
        { type: "snackbar" }
      );
    } finally {
      setIsLoading(false);
    }
  };

  // convert single value to array.
  const disabledItems = !Array.isArray(value) ? [value] : value;
  return (
    <EntitySearchDropdown
      isLoading={isLoading}
      options={videoList || []}
      search={search}
      onSearch={setSearch}
      onSelect={handleSelection}
      hasMore={hasMore && !search}
      onNextPage={nextPage}
      onOpen={setDropdownOpen}
      renderToggle={({ isOpen, onToggle }) => (
        <Button variant="primary" onClick={onToggle} aria-expanded={isOpen}>
          {__("Create or select media", "presto-player")}
        </Button>
      )}
      renderItem={({ item, onSelect }) => {
        const { id, title, details } = item;
        const { type, name } = details || {};
        const thumbnail =
          item?._embedded?.["wp:featuredmedia"]?.[0]?.source_url || "";
        return (
          <MenuItem
            icon={<VideoIcon thumbnail={thumbnail} type={type} />}
            iconPosition="left"
            suffix={type ? name : __("Choose media", "presto-player")}
            onClick={() => onSelect(item)}
            disabled={(disabledItems || []).includes(id)}
            key={id}
            css={css`
              .components-menu-item__item {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                display: inline-block;
                text-align: left;
              }
            `}
          >
            {title || __("Untitled", "presto-player")}
          </MenuItem>
        );
      }}
      {...props}
    />
  );
};

export default SelectMediaDropdown;

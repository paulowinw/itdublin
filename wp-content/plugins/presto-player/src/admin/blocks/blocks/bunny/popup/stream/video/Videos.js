/** @jsx jsx */
const { __ } = wp.i18n;
const { useState, useEffect, Fragment } = wp.element;
const { Flex, FormFileUpload, Notice } = wp.components;
const { dispatch, useSelect } = wp.data;
import { css, jsx } from "@emotion/core";
import Video from "./Video";

import Loading from "../Loading";

export default () => {
  const [selected, setSelected] = useState(false);
  const [notice, setNotice] = useState("");
  const fetched = useSelect((select) =>
    select("presto-player/bunny-popup").videosFetched()
  );
  const videos = useSelect((select) =>
    select("presto-player/bunny-popup").videos()
  );
  const type = useSelect((select) =>
    select("presto-player/bunny-popup").requestType()
  );
  const collection = useSelect((select) =>
    select("presto-player/bunny-popup").currentCollection()
  );

  // fetch videos
  useEffect(() => {
    if (!fetched) {
      fetch();
    }
  }, [fetched]);

  // sync every 5 seconds
  useEffect(() => {
    const interval = setInterval(() => {
      fetch({ sync: true });
    }, 3000);
    return () => clearInterval(interval);
  }, [fetched]);

  // fetch videos
  const fetch = async ({ sync } = { sync: false }) => {
    try {
      const videos = await wp.apiFetch({
        path: wp.url.addQueryArgs(`presto-player/v1/bunny/stream/videos`, {
          type,
          ...(collection?.guid ? { collection: collection?.guid } : {}),
          items_per_page: 500,
        }),
      });
      dispatch("presto-player/bunny-popup").setVideos(videos?.items);
      setNotice("");
    } catch (e) {
      if (e?.data?.status === 401) {
        setNotice(
          __(
            "Please wait. Pullzone cache is clearing. This may take a minute or two..."
          )
        );
        return;
      }

      if (!sync) {
        dispatch("presto-player/bunny-popup").addError(e.message);
      }
    } finally {
      dispatch("presto-player/bunny-popup").setVideosFetched(true);
    }
  };

  // default no video content
  const renderDefaultContent = () => {
    return (
      <Flex
        align="center"
        justify="center"
        css={css`
          height: 100%;
          text-align: center;
        `}
      >
        <div>
          <h2>{__("Drop video files here to upload", "presto-player")}</h2>
          <p>{__("or browse for a video", "presto-player")}</p>
          <FormFileUpload
            isSecondary
            accept="video/mp4,video/x-m4v,video/*"
            onChange={(e) => {
              if (!e.target.files) {
                return;
              }
              dispatch("presto-player/bunny-popup").addUploads(e.target.files);
              jQuery(e.target).val(null);
            }}
          >
            {__("Upload New Video", "presto-player")}
          </FormFileUpload>
        </div>
      </Flex>
    );
  };

  if (notice) {
    return (
      <Notice status="warning" isDismissible={false}>
        <div css={{ display: "flex", alignItems: "center" }}>
          <Loading css={{ flex: 1 }} />
          {notice}
        </div>
      </Notice>
    );
  }

  // still loading
  if (!fetched) {
    return <Loading css={{ flex: 1 }} />;
  }

  // render
  return (
    <Fragment>
      {videos && !!videos.length ? (
        <div>
          {!collection?.guid && <h2>{__("Videos", "presto-player")}</h2>}
          {videos.map((video) => {
            if (video?.collectionId !== (collection?.guid || "")) {
              return;
            }
            return (
              <Video
                key={video.id}
                video={video}
                onClick={() => {
                  setSelected(selected.guid === video.guid ? {} : video);
                }}
                selected={selected.guid === video.guid}
              />
            );
          })}
        </div>
      ) : (
        renderDefaultContent()
      )}
    </Fragment>
  );
};

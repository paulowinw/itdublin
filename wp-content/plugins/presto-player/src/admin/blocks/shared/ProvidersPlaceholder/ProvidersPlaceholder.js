/** @jsx jsx */
import { css, jsx } from "@emotion/core";
import { store as blockEditorStore } from "@wordpress/block-editor";
import { createBlock } from "@wordpress/blocks";
import {
  Placeholder,
  Flex,
  FlexItem,
  Spinner,
  Button,
  MenuItem,
} from "@wordpress/components";
import { useDispatch, dispatch } from "@wordpress/data";
import { useState, useEffect } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { store as coreStore } from "@wordpress/core-data";
import { store as noticesStore } from "@wordpress/notices";
import VideoProvider from "./components/VideoProvider";
import providerIcons from "./icons";
import Separator from "./components/Separator";
import SelectMediaDropdown from "../components/SelectMediaDropdown";
import VideoIcon from "../components/VideoIcon";

const ProvidersPlaceholder = ({
  clientId,
  shouldInsertBlock = true,
  selectExisting = false,
  sync,
}) => {
  const [saving, setSaving] = useState(false);
  const [ID, setID] = useState("");
  const { createErrorNotice } = useDispatch(noticesStore);
  const { saveEntityRecord } = useDispatch(coreStore);
  const { replaceBlock } = useDispatch(blockEditorStore);
  const { insertBlock } = useDispatch(blockEditorStore);

  // Replace current block with the selected video block.
  useEffect(() => {
    if (!ID) return;
    replaceBlock(
      clientId,
      createBlock("presto-player/reusable-display", {
        id: ID,
      })
    );
  }, [ID]);

  const createVideo = async (videoType) => {
    if (saving) return;
    try {
      setSaving(true);
      const { id } = await saveEntityRecord(
        "postType",
        "pp_video_block",
        {
          status: "publish",
          content: `<!-- wp:presto-player/reusable-edit -->
            <div class="wp-block-presto-player-reusable-edit"><!-- wp:presto-player/${videoType} /--></div>
            <!-- /wp:presto-player/reusable-edit -->`,
        },
        { throwOnError: true }
      );
      setID(id);
    } catch (e) {
      createErrorNotice(
        e?.message || __("Something went wrong", "presto-player")
      );
    } finally {
      setSaving(false);
    }
  };

  const handleCreate = (type) => {
    // For media hub video blocks, directly insert blocks.
    if (shouldInsertBlock) {
      insertBlock(createBlock(`presto-player/${type}`), 0, clientId);
      return;
    }
    // if media hub sync is turned off, we can directly create and replace the block.
    if (!sync) {
      const newBlock = createBlock(`presto-player/${type}`);
      replaceBlock(clientId, newBlock);
      return;
    }
    // sync with media hub, by creating a video.
    createVideo(type);
  };

  if (saving) {
    return (
      <Placeholder
        css={css`
          &.components-placeholder {
            padding: 16px;
          }
        `}
      >
        <Spinner />
      </Placeholder>
    );
  }

  return (
    <Placeholder
      css={css`
        &.components-placeholder {
          padding: 32px;
        }
      `}
      label={
        <>
          <Flex
            direction="column"
            css={css`
              margin-bottom: 4px;
            `}
            gap="16px"
          >
            <Flex justify="flex-start">
              {providerIcons.mediaHubBlock}
              <h1
                css={css`
                  font-size: 24px !important;
                  font-weight: 500 !important;
                  margin: 0px !important;
                `}
              >
                {__("Presto Player", "presto-player")}
              </h1>
            </Flex>
            <Flex>
              <p
                css={css`
                  font-size: 14px !important;
                  font-weight: 300 !important;
                  margin: 0px !important;
                `}
              >
                {__("Choose a video type to get started.", "presto-player")}
              </p>
            </Flex>
          </Flex>
        </>
      }
    >
      <Flex
        direction="column"
        css={css`
          max-width: 540px;
          width: 100%;
        `}
        gap="20px"
      >
        <Flex
          justify={"start"}
          css={css`
            width: 100%;
            max-width: 100%;
          `}
          wrap="wrap"
          gap="20px"
        >
          <FlexItem>
            <VideoProvider
              provider={__("Video", "presto-player")}
              onCreate={() => handleCreate("self-hosted")}
              icon={providerIcons.video}
            />
          </FlexItem>
          {!!prestoPlayer?.isPremium && (
            <FlexItem>
              <VideoProvider
                provider={__("Bunny.net", "presto-player")}
                onCreate={() => handleCreate("bunny")}
                icon={providerIcons.bunny}
              />
            </FlexItem>
          )}
          <FlexItem>
            <VideoProvider
              provider={__("YouTube", "presto-player")}
              onCreate={() => handleCreate("youtube")}
              icon={providerIcons.youtube}
            />
          </FlexItem>
          <FlexItem>
            <VideoProvider
              provider={__("Vimeo", "presto-player")}
              onCreate={() => handleCreate("vimeo")}
              icon={providerIcons.vimeo}
            />
          </FlexItem>
          <FlexItem>
            <VideoProvider
              provider={__("Audio", "presto-player")}
              onCreate={() => handleCreate("audio")}
              icon={providerIcons.audio}
            />
          </FlexItem>
          {/* free preview for bunny block */}
          {!prestoPlayer?.isPremium && (
            <FlexItem>
              <VideoProvider
                provider={__("Bunny.net", "presto-player")}
                onCreate={() => {
                  dispatch("presto-player/player").setProModal(true);
                  return;
                }}
                icon={providerIcons.bunny}
                pro={true}
              />
            </FlexItem>
          )}
        </Flex>
        {selectExisting && (
          <>
            <Separator icon={providerIcons.line} />
            <Flex>
              <SelectMediaDropdown
                popoverProps={{ placement: "bottom-start" }}
                onSelect={({ id }) => {
                  replaceBlock(
                    clientId,
                    createBlock("presto-player/reusable-display", {
                      id,
                    })
                  );
                }}
                renderToggle={({ isOpen, onToggle }) => (
                  <Button
                    variant="primary"
                    onClick={onToggle}
                    aria-expanded={isOpen}
                  >
                    {__("Select media", "presto-player")}
                  </Button>
                )}
                renderItem={({ item, onSelect }) => {
                  const { id, title, details } = item;
                  const { type, name } = details || {};
                  const thumbnail =
                    item?._embedded?.["wp:featuredmedia"]?.[0]?.source_url ||
                    "";
                  return (
                    <MenuItem
                      icon={<VideoIcon thumbnail={thumbnail} type={type} />}
                      iconPosition="left"
                      suffix={type ? name : __("Choose media", "presto-player")}
                      onClick={() => onSelect(item)}
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
              />
            </Flex>
          </>
        )}
      </Flex>
    </Placeholder>
  );
};

export default ProvidersPlaceholder;

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { getBlobByURL, isBlobURL } = wp.blob;
const { Button, Disabled, Toolbar, Notice, withNotices, Placeholder, Spinner } =
  wp.components;

import { BlockControls, InspectorControls } from "@wordpress/block-editor";
const { compose } = wp.compose;
const { useEffect, useState } = wp.element;
const { dispatch } = wp.data;
const { createBlock } = wp.blocks;

import { isHLS } from "@/shared/util";

// hocs
import withPlayerEdit from "../with-player-edit";
import withPlayerData from "../with-player-data";

/**
 * Internal dependencies
 */
import HostedPlaceholder from "./HostedPlaceholder";
import TracksEditor from "@/admin/blocks/shared/tracks/TracksEditor";
import BlockInspectorControls from "@/admin/blocks/shared/BlockInspectorControls";
import Player from "@/admin/blocks/shared/Player";

import { determineVideoUrlType } from "@/shared/util.js";
import Editing from "../../shared/Editing";

//constants
const ALLOWED_MEDIA_TYPES = ["video"];

export default compose([withPlayerData(), withPlayerEdit()])(
  withNotices(
    ({
      attributes,
      setAttributes,
      noticeOperations,
      branding,
      loading,
      presetData,
      createVideo,
      onRemoveSrc,
      renderKey,
      lockSave,
      unlockSave,
      clientId,
      defaultPreset,
    }) => {
      const { poster, src, id, tracks } = attributes;
      const [upgradeNotice, setUpgradeNotice] = useState("");

      const showNotice = (e) => {
        noticeOperations.removeAllNotices();
        noticeOperations.createErrorNotice(e?.message);
      };

      useEffect(() => {
        if (!id && isBlobURL(src)) {
          const file = getBlobByURL(src);
          if (file) {
            mediaUpload({
              filesList: [file],
              onFileChange: ([{ url }]) => {
                setAttributes({ src: url });
              },
              onError: (message) => {
                noticeOperations.createErrorNotice(message);
              },
              allowedTypes: ALLOWED_MEDIA_TYPES,
            });
          }
        }
      }, []);

      function onSelectVideo(media) {
        if (!media || !media.url) {
          // in this case there was an error
          // previous attributes should be removed
          // because they may be temporary blob urls
          setAttributes({ src: undefined, id: undefined });
          return;
        }
        // sets the block's attribute and updates the edit component from the
        // selected media
        setAttributes({
          src: media.url,
          preset: defaultPreset?.id,
          attachment_id: media.id,
        });

        lockSave();
        createVideo({
          src: media.url,
          type: "attachment",
          attachment_id: media.id,
        })
          .catch((e) => {
            setAttributes({ src: "" });
            showNotice(e);
          })
          .finally(unlockSave);
      }

      function onSelectURL(newSrc) {
        setAttributes({ attachment_id: null });
        if (newSrc && isHLS(newSrc)) {
          if (!prestoPlayer?.isPremium) {
            setUpgradeNotice(
              <Notice status="info" onRemove={() => setUpgradeNotice("")}>
                <div>
                  <div>
                    <strong>
                      {__(
                        "Get HLS Streaming and more with Presto Player Pro!",
                        "presto-player"
                      )}
                    </strong>
                  </div>

                  {__(
                    "Stream HLS links and more with Presto Player Pro.",
                    "presto-player"
                  )}
                  <div
                    style={{
                      marginTop: "1em",
                    }}
                  >
                    <Button isPrimary>{__("Upgrade", "presto-player")}</Button>
                  </div>
                </div>
              </Notice>
            );
            return;
          }
        }

        if (newSrc !== src) {
          const { type } = determineVideoUrlType(newSrc);
          if (type === "youtube") {
            const youtubeBlock = createBlock("presto-player/youtube", {
              src: newSrc,
            });
            dispatch("core/editor").replaceBlock(clientId, youtubeBlock);
            return;
          }

          if (type === "vimeo") {
            const vimeoBlock = createBlock("presto-player/vimeo", {
              src: newSrc,
            });
            dispatch("core/editor").replaceBlock(clientId, vimeoBlock);
            return;
          }

          setAttributes({ src: newSrc, attachmend_id: null });
          setAttributes({ preset: defaultPreset?.id });

          lockSave();
          createVideo({ src: newSrc, type: "link" })
            .catch((e) => {
              setAttributes({ src: "" });
              showNotice(e);
            })
            .finally(unlockSave);
        }
      }

      function onUploadError(message) {
        noticeOperations.removeAllNotices();
        noticeOperations.createErrorNotice(message);
      }

      if (!src) {
        return (
          <div>
            <HostedPlaceholder
              onSelect={onSelectVideo}
              onSelectURL={onSelectURL}
              setAttributes={setAttributes}
              attributes={attributes}
              onError={onUploadError}
            >
              {upgradeNotice}
            </HostedPlaceholder>
          </div>
        );
      }

      // loading presets still
      if (loading || !id) {
        return (
          <Placeholder className="presto-player__placeholder is-loading">
            <Spinner />
          </Placeholder>
        );
      }

      return (
        <div>
          <BlockControls>
            <TracksEditor
              tracks={tracks}
              onChange={(newTracks) => {
                setAttributes({ tracks: newTracks });
              }}
            />
            <Toolbar>
              <Button onClick={() => onRemoveSrc()}>
                {__("Replace", "presto-player")}
              </Button>
            </Toolbar>
            <Editing />
          </BlockControls>

          <InspectorControls>
            <BlockInspectorControls
              setAttributes={setAttributes}
              attributes={attributes}
            />
          </InspectorControls>

          <figure>
            {/*
            Disable the video tag so the user clicking on it won't play the
            video when the controls are enabled.
            */}
            <Disabled>
              <Player
                poster={poster}
                src={src}
                id={id}
                type={"hosted"}
                attributes={attributes}
                setAttributes={setAttributes}
                preset={presetData}
                branding={branding}
                key={renderKey}
              />
            </Disabled>
          </figure>
        </div>
      );
    }
  )
);

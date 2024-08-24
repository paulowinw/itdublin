/**
 * WordPress dependencies
 */
import {
  Button,
  Disabled,
  DropdownMenu,
  Spinner,
  Toolbar,
  withNotices,
  BaseControl,
  Placeholder,
  ToggleControl,
} from "@wordpress/components";
import { BlockControls, InspectorControls } from "@wordpress/block-editor";
import { __ } from "@wordpress/i18n";
import { compose } from "@wordpress/compose";
import { useEffect, useState, Fragment } from "@wordpress/element";
import { dispatch } from "@wordpress/data";

import { signURL } from "../../shared/services/bunny";

// hocs
import withPlayerEdit from "../with-player-edit";
import withPlayerData from "../with-player-data";

/**
 * Internal dependencies
 */
import TracksEditor from "@/admin/blocks/shared/tracks/TracksEditor";
import StorageMedia from "./StorageMedia";
import StreamMedia from "./StreamMedia";
import APIPlaceholder from "./APIPlaceholder";
import BlockInspectorControls from "@/admin/blocks/shared/BlockInspectorControls";
import Player from "@/admin/blocks/shared/Player";
import Editing from "../../shared/Editing";

export default compose([withPlayerData(), withPlayerEdit()])(
  withNotices(
    ({
      attributes,
      setAttributes,
      noticeOperations,
      branding,
      isSelected,
      presetData,
      createVideo,
      lockSave,
      unlockSave,
      onRemoveSrc,
      renderKey,
      defaultPreset,
    }) => {
      const { poster, src, id, tracks, visibility, previewSrc, thumbnail } =
        attributes;

      const [mediaPopup, setMediaPopup] = useState("");
      const [loading, setLoading] = useState(false);

      // What setup screen should we show
      const [setup, setSetup] = useState("");
      const [isSetup, setIsSetup] = useState({
        stream: false,
        storage: false,
      });
      const [disableStream, setDisableStream] = useState(false);

      // setup and api
      const [isAPILoaded, setIsAPILoaded] = useState(false);
      const [fetchingSettings, setFetchingSettings] = useState(false);
      const [autoSubmitStream, setAutoSubmitStream] = useState(false);

      const userCanReadSettings = wp.data.useSelect((select) =>
        select("core").canUser("read", "settings")
      );

      // is this private
      const isPrivate = visibility === "private";

      // is legacy storage disabled
      const disableLegacyStorage =
        prestoPlayerAdmin?.bunny?.disable_legacy_storage;

      // set privacy option in store
      useEffect(() => {
        dispatch("presto-player/bunny-popup").setIsPrivate(
          visibility === "private"
        );
      }, [visibility]);

      useEffect(() => {
        setIsSetup({
          storage: prestoPlayerAdmin?.isSetup?.bunny?.storage,
          stream: prestoPlayerAdmin?.isSetup?.bunny?.stream,
        });
      }, []);

      function selectVideo(media, ...args) {
        if (!media.url) {
          // in this case there was an error
          // previous attributes should be removed
          // because they may be temporary blob urls
          setAttributes({ src: undefined, id: undefined });
          return;
        }

        // set preset attributes
        setAttributes({
          src: media.url,
          preset: defaultPreset?.id,
          ...(media?.thumbnail ? { thumbnail: media.thumbnail } : {}),
          ...(media?.preview ? { preview: media.preview } : {}),
        });

        // create video
        setLoading(true);
        lockSave();
        createVideo({
          src: media.url,
          type: "bunny",
          title: media.title,
          ...(media?.guid ? { external_id: media.guid } : {}),
        })
          .catch((e) => {
            setAttributes({ src: "" });
            console.error(e);
          })
          .finally(() => {
            unlockSave();
            setLoading(false);
          });
      }

      // fetch settings
      const fetchSettings = async () => {
        try {
          const {
            presto_player_bunny_pull_zones,
            presto_player_bunny_storage_zones,
            presto_player_bunny_stream_private,
            presto_player_bunny_stream_public,
          } = await wp.apiFetch({
            path: `wp/v2/settings`,
          });
          setIsAPILoaded(true);

          if (!presto_player_bunny_stream_private) {
            setDisableStream(true);
          }

          // is this set up?
          const isSetup = (option) => {
            return !!(option?.private_id && option?.public_id);
          };

          const storageSetup =
            isSetup(presto_player_bunny_pull_zones) ||
            isSetup(presto_player_bunny_storage_zones);
          const streamSetup =
            presto_player_bunny_stream_private?.pull_zone_url &&
            presto_player_bunny_stream_public?.pull_zone_url;

          setIsSetup({
            storage: storageSetup,
            stream: streamSetup,
          });

          if (!storageSetup && !streamSetup) {
            setSetup("stream");
          } else {
            setSetup("");
          }
        } finally {
          setFetchingSettings(false);
        }
      };

      // mounted
      useEffect(() => {
        if (userCanReadSettings) {
          fetchSettings();
        } else {
          setIsAPILoaded(true);
        }
      }, [userCanReadSettings]);

      const setPreview = async () => {
        if (isPrivate) {
          let previewSrc = await signURL(src);
          setAttributes({ previewSrc });
        } else {
          setAttributes({ previewSrc: src });
        }
      };
      useEffect(() => {
        setPreview();
      }, [src]);

      const setThumbnail = async () => {
        if (isPrivate) {
          let previewThumbnail = await signURL(thumbnail);
          if (previewThumbnail) {
            setAttributes({ previewThumbnail });
          }
        } else {
          setAttributes({ previewThumbnail: thumbnail });
        }
      };
      useEffect(() => {
        setThumbnail();
      }, [thumbnail]);

      const placeholderButtons = () => {
        return (
          <Fragment>
            {isSetup.stream && (
              <Button isPrimary onClick={() => setMediaPopup("stream")}>
                {isPrivate
                  ? __("Add/Select Private Video Stream", "presto-player")
                  : __("Add/Select Video Stream", "presto-player")}
              </Button>
            )}

            {!disableLegacyStorage && isSetup.storage && (
              <Button
                isSecondary={isSetup.stream}
                isPrimary={!isSetup.stream}
                onClick={() => setMediaPopup("storage")}
              >
                {isPrivate
                  ? __("Add/Select Private Video (Classic)", "presto-player")
                  : __("Add/Select Video (Classic)", "presto-player")}
              </Button>
            )}

            {!isSetup.stream && !disableStream && isSetup.storage && (
              <Button
                isSecondary
                onClick={() => {
                  setAutoSubmitStream(true);
                  setSetup("stream");
                }}
              >
                {__("Enable Bunny.net Stream!", "presto-player")}
              </Button>
            )}

            {!!userCanReadSettings && moreButtons()}
          </Fragment>
        );
      };

      const moreButtons = () => {
        const controls = [];
        !disableStream &&
          controls.push({
            title: isSetup.stream
              ? __("Reconnect Stream", "presto-player")
              : __("Connect Stream", "presto-player"),
            onClick: () => {
              setSetup("stream");
              setAutoSubmitStream(false);
            },
          });

        !disableLegacyStorage &&
          controls.push({
            title: isSetup.storage
              ? __("Reconnect Storage (Classic)", "presto-player")
              : __("Connect Storage (Classic)", "presto-player"),
            onClick: () => setSetup("storage"),
          });

        return (
          <DropdownMenu
            icon="ellipsis"
            label={__("Connection Options", "presto-player")}
            controls={controls}
          />
        );
      };

      if (loading || !isAPILoaded) {
        return (
          <Placeholder className="presto-player__placeholder is-loading">
            <Spinner />
          </Placeholder>
        );
      }

      if (setup === "stream") {
        return (
          <APIPlaceholder
            type="stream"
            autoSubmit={autoSubmitStream}
            onRefetch={() => {
              setIsAPILoaded(false);
              fetchSettings();
            }}
          />
        );
      }

      if (setup === "storage") {
        return (
          <APIPlaceholder
            type="storage"
            onRefetch={() => {
              setIsAPILoaded(false);
              fetchSettings();
            }}
          />
        );
      }

      if (!id) {
        return (
          <div>
            <Placeholder
              label={
                isPrivate
                  ? __("Bunny.net Private Video", "presto-player")
                  : __("Bunny.net Video", "presto-player")
              }
              icon={
                isPrivate ? (
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="2"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    className="presto-block-icon"
                  >
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                  </svg>
                ) : (
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="2"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    className="presto-block-icon"
                  >
                    <polyline points="8 17 12 21 16 17"></polyline>
                    <line x1="12" y1="12" x2="12" y2="21"></line>
                    <path d="M20.88 18.09A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.29"></path>
                  </svg>
                )
              }
              instructions={__(
                "Add or select a Bunny.net video",
                "presto-player"
              )}
            >
              <BaseControl className="presto-player__placeholder-control">
                <ToggleControl
                  label="Private Video"
                  help={
                    isPrivate
                      ? "Video is only accessible to those who are logged in."
                      : "Video is accessible to everyone."
                  }
                  checked={isPrivate}
                  onChange={(isPrivate) => {
                    setAttributes({
                      visibility: isPrivate ? "private" : "public",
                    });
                  }}
                />
              </BaseControl>
              {placeholderButtons()}
            </Placeholder>

            {mediaPopup === "storage" && (
              <StorageMedia
                isPrivate={isPrivate}
                closePopup={() => setMediaPopup("")}
                noticeOperations={noticeOperations}
                onSelect={selectVideo}
              />
            )}

            {mediaPopup === "stream" && (
              <StreamMedia
                isPrivate={isPrivate}
                closePopup={() => setMediaPopup("")}
                noticeOperations={noticeOperations}
                onSelect={selectVideo}
              />
            )}
          </div>
        );
      }

      return (
        <>
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

          <figure className="wp-block-video">
            {/*
            Disable the video tag so the user clicking on it won't play the
            video when the controls are enabled.
            */}
            <Disabled>
              <Player
                poster={poster}
                src={previewSrc}
                id={id}
                type={"bunny"}
                attributes={attributes}
                setAttributes={setAttributes}
                preset={presetData}
                branding={branding}
                key={renderKey}
              />
            </Disabled>
          </figure>
        </>
      );
    }
  )
);

/**
 * WordPress dependencies
 */
const { Button, Disabled, Toolbar, Placeholder, Spinner, withNotices } =
  wp.components;
import { BlockControls, InspectorControls } from "@wordpress/block-editor";
const { useEffect } = wp.element;
const { __ } = wp.i18n;
const { compose } = wp.compose;

// hocs
import withPlayerEdit from "../with-player-edit";
import withPlayerData from "../with-player-data";

/**
 * Internal dependencies
 */
import BlockInspectorControls from "@/admin/blocks/shared/BlockInspectorControls";
import Player from "@/admin/blocks/shared/Player";
import LinkPlaceholder from "@/admin/blocks/shared/LinkPlaceholder";
import { getYoutubeId } from "@/shared/util.js";
import { usePrevious } from "@/admin/blocks/util";
import Editing from "../../shared/Editing";

export default compose([withPlayerData(), withPlayerEdit()])(
  withNotices(
    ({
      attributes,
      setAttributes,
      noticeOperations,
      branding,
      createVideo,
      loading,
      presetData,
      onRemoveSrc,
      lockSave,
      unlockSave,
      renderKey,
    }) => {
      const { poster, src, id } = attributes;

      const showNotice = (e) => {
        noticeOperations.removeAllNotices();
        noticeOperations.createErrorNotice(e?.message);
      };

      // make sure to save/get attachment id if src is set
      const prevSrc = usePrevious(src);
      useEffect(() => {
        // reset id only if we're changing the src
        if (prevSrc) {
          setAttributes({ id: 0 });
        }
        let video_id = getYoutubeId(src);
        setAttributes({ video_id });

        lockSave();
        createVideo({ src, external_id: video_id, type: "youtube" })
          .catch((e) => {
            setAttributes({ src: "" });
            showNotice(e);
          })
          .finally(unlockSave);
      }, [src]);

      // handle url update
      function onSelectURL(newSrc) {
        if (newSrc !== src) {
          setAttributes({ src: newSrc });
        }
      }

      if (!src) {
        return (
          <div>
            <LinkPlaceholder
              icon={
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
                  <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path>
                  <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon>
                </svg>
              }
              label={__("Presto Youtube Video", "presto-player")}
              instructions={__("Enter Youtube Video URL", "presto-player")}
              placeholder={__("Youtube URL", "presto-player")}
              attributes={attributes}
              setAttributes={setAttributes}
              onSelectURL={onSelectURL}
            />
          </div>
        );
      }

      // loading presets or id still
      if (loading || !id) {
        return (
          <Placeholder className="presto-player__placeholder is-loading">
            <Spinner />
          </Placeholder>
        );
      }

      return (
        <>
          <BlockControls>
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
                type="youtube"
                id={id}
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

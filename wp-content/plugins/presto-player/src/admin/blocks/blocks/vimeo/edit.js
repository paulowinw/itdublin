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
// import TracksEditor from "@/admin/blocks/shared/tracks/TracksEditor";
import BlockInspectorControls from "@/admin/blocks/shared/BlockInspectorControls";
import Player from "@/admin/blocks/shared/Player";
import LinkPlaceholder from "@/admin/blocks/shared/LinkPlaceholder";
import { getVimeoId } from "@/shared/util.js";
import { usePrevious } from "@/admin/blocks/util";
import Editing from "../../shared/Editing";

export default compose([withPlayerData(), withPlayerEdit()])(
  withNotices(
    ({
      attributes,
      setAttributes,
      branding,
      noticeOperations,
      loading,
      createVideo,
      lockSave,
      unlockSave,
      presetData,
      onRemoveSrc,
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
        let video_id = getVimeoId(src);
        setAttributes({ video_id });

        lockSave();
        createVideo({ src, external_id: video_id, type: "vimeo" })
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
                  <path d="M22.875 10.063c-2.442 5.217-8.337 12.319-12.063 12.319-3.672 0-4.203-7.831-6.208-13.043-.987-2.565-1.624-1.976-3.474-.681l-1.128-1.455c2.698-2.372 5.398-5.127 7.057-5.28 1.868-.179 3.018 1.098 3.448 3.832.568 3.593 1.362 9.17 2.748 9.17 1.08 0 3.741-4.424 3.878-6.006.243-2.316-1.703-2.386-3.392-1.663 2.673-8.754 13.793-7.142 9.134 2.807z" />
                </svg>
              }
              label={__("Presto Vimeo Video", "presto-player")}
              instructions={__("Enter Vimeo Video URL", "presto-player")}
              placeholder={__("Vimeo URL", "presto-player")}
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
                type="vimeo"
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

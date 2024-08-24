/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const {
  PanelRow,
  TextControl,
  SelectControl,
  BaseControl,
  Button,
  FocalPointPicker,
  RangeControl,
} = wp.components;
const { MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { useState } = wp.element;

const VIDEO_OVERLAY_ALLOWED_MEDIA_TYPES = ["image"];

// import font for text
import "./settings-styles.css";

export default ({ attributes, setAttributes, instanceId }) => {
  const { mutedOverlay } = attributes;

  const videoOverlayDescription = `video-block__overlay-image-description-${instanceId}`;

  // handle poster select
  function onSelectOverlay(image) {
    setAttributes({
      mutedOverlay: {
        ...mutedOverlay,
        ...{ src: image.url },
      },
    });
  }

  function onRemoveOverlay() {
    setAttributes({
      mutedOverlay: {
        ...mutedOverlay,
        ...{ src: "" },
      },
    });
  }

  return (
    <>
      <MediaUploadCheck>
        <BaseControl className="editor-video-overlay-control">
          <BaseControl.VisualLabel>
            <p>{__("Overlay Image", "presto-player")}</p>
          </BaseControl.VisualLabel>
          <MediaUpload
            title={__("Select overlay image", "presto-player")}
            onSelect={onSelectOverlay}
            allowedTypes={VIDEO_OVERLAY_ALLOWED_MEDIA_TYPES}
            render={({ open }) => (
              <Button
                className="presto-setting__poster"
                isPrimary
                onClick={open}
              >
                {!mutedOverlay?.src
                  ? __("Select", "presto-player")
                  : __("Replace", "presto-player")}
              </Button>
            )}
          />
          <p id={videoOverlayDescription} hidden>
            {mutedOverlay?.src
              ? sprintf(
                  __("The current overlay image url is %s", "presto-player"),
                  mutedOverlay?.src
                )
              : __(
                  "There is no overlay image currently selected",
                  "presto-player"
                )}
          </p>
          {!!mutedOverlay?.src && (
            <Button
              onClick={onRemoveOverlay}
              className="presto-setting__remove-poster"
              isTertiary
            >
              {__("Remove", "presto-player")}
            </Button>
          )}
        </BaseControl>
      </MediaUploadCheck>
      {!!mutedOverlay?.src && (
        <FocalPointPicker
          url={""}
          dimensions={{ width: 160, height: 90 }}
          value={mutedOverlay?.focalPoint}
          onChange={(focalPoint) =>
            setAttributes({
              mutedOverlay: {
                ...mutedOverlay,
                ...{ focalPoint },
              },
            })
          }
        />
      )}
      {!!mutedOverlay?.src && (
        <RangeControl
          label={__("Max Width (%)", "presto-player")}
          value={mutedOverlay?.width}
          onChange={(width) =>
            setAttributes({
              mutedOverlay: {
                ...mutedOverlay,
                ...{ width },
              },
            })
          }
          min={1}
          max={100}
        />
      )}
    </>
  );
};

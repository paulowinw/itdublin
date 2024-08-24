/**
 * External dependencies
 */

import classnames from "classnames";
const baseCurrentUrl = window.location.href;
import helpers from "./helpers";

/**
 * WordPress dependencies
 */
const {
  Button,
  Notice,
  Placeholder,
  DropZone,
  withFilters,
  BaseControl,
  ToggleControl,
  FormFileUpload,
} = wp.components;

const { __ } = wp.i18n;
const { useState, useEffect } = wp.element;
const { useSelect } = wp.data;
const deprecated = wp.deprecated;
const { MediaUpload, MediaUploadCheck, URLPopover } = wp.editor;
// const { URLPopover } = wp.blockEditor;

const InsertFromURLPopover = ({ src, onChange, onSubmit, onClose }) => (
  <URLPopover onClose={onClose}>
    <form
      className="block-editor-media-placeholder__url-input-form"
      onSubmit={onSubmit}
    >
      <input
        data-cy="url-input"
        className="block-editor-media-placeholder__url-input-field"
        type="url"
        aria-label={__("URL", "presto-player")}
        placeholder={__(
          "Paste or type a Youtube, Vimeo or .mp4 video URL",
          "presto-player"
        )}
        onChange={onChange}
        value={src}
      />
      <Button
        data-cy="url-submit"
        className="block-editor-media-placeholder__url-input-submit-button"
        icon={"editor-break"}
        label={__("Apply", "presto-player")}
        type="submit"
      />
    </form>
  </URLPopover>
);

export function MediaPlaceholder({
  value = {},
  allowedTypes = [],
  className,
  icon,
  url = true,
  labels = {},
  mediaPreview,
  notices,
  isAppender,
  isPrivate,
  addToGallery,
  onSelect,
  onCancel,
  onSelectURL,
  onDoubleClick,
  children,
  allowURLs,
}) {
  const mediaUpload = useSelect((select) => {
    const { getSettings } = select("core/block-editor");
    return getSettings().mediaUpload;
  }, []);

  const [src, setSrc] = useState("");
  const [isURLInputVisible, setIsURLInputVisible] = useState(false);

  useEffect(() => {
    setSrc(value?.src ?? "");
  }, [value]);

  const onChangeSrc = (event) => {
    setSrc(event.target.value);
  };

  const openURLInput = () => {
    setIsURLInputVisible(true);
  };

  const closeURLInput = () => {
    setIsURLInputVisible(false);
  };

  const onSubmitSrc = (event) => {
    event.preventDefault();
    if (src && onSelectURL) {
      onSelectURL(src);
      closeURLInput();
    }
  };

  const renderPlaceholder = (content, onClick) => {
    let { instructions, title } = labels;

    if (!mediaUpload && !onSelectURL) {
      instructions = __(
        "To edit this block, you need permission to upload media.",
        "presto-player"
      );
    }

    // set class names
    const placeholderClassName = classnames(
      "block-editor-media-placeholder",
      className,
      {
        "is-appender": isAppender,
      }
    );

    return (
      <Placeholder
        icon={icon}
        label={title}
        instructions={instructions}
        className={placeholderClassName}
        notices={notices}
        onClick={onClick}
        onDoubleClick={onDoubleClick}
        preview={mediaPreview}
      >
        {children}
        {content}
      </Placeholder>
    );
  };

  const renderCancelLink = () => {
    return (
      onCancel && (
        <Button
          className="block-editor-media-placeholder__cancel-button"
          title={__("Cancel", "presto-player")}
          isLink
          onClick={onCancel}
        >
          {__("Cancel", "presto-player")}
        </Button>
      )
    );
  };

  const renderUrlSelectionUI = () => {
    return (
      onSelectURL && (
        <div className="block-editor-media-placeholder__url-input-container">
          {url && (
            <Button
              data-cy="video-url"
              className="block-editor-media-placeholder__button"
              onClick={openURLInput}
              isPressed={isURLInputVisible}
              isTertiary
            >
              {__("Video URL", "presto-player")}
            </Button>
          )}
          {isURLInputVisible && (
            <InsertFromURLPopover
              src={src}
              onChange={onChangeSrc}
              onSubmit={onSubmitSrc}
              onClose={closeURLInput}
            />
          )}
        </div>
      )
    );
  };

  const renderMediaUploadChecked = () => {
    const mediaLibraryButton = (
      <MediaUpload
        title={
          isPrivate
            ? __("Select or Upload Private Video", "presto-player")
            : __("Select or Upload Video", "presto-player")
        }
        addToGallery={addToGallery}
        gallery={false}
        multiple={false}
        onSelect={(event) => {
          // set private/public url params
          helpers.unsetUrlParams();
          onSelect(event);
        }}
        onClose={() => {
          // unset private/public url params
          helpers.unsetUrlParams();
        }}
        allowedTypes={allowedTypes}
        value={Array.isArray(value) ? value.map(({ id }) => id) : value.id}
        render={({ open }) => {
          return (
            <Button
              data-cy="add-video"
              isPrimary
              onClick={(event) => {
                event.stopPropagation();
                helpers.unsetUrlParams();
                if (isPrivate) {
                  helpers.setUrlPrivate(baseCurrentUrl);
                } else {
                  helpers.setUrlPublic(baseCurrentUrl);
                }
                open();
              }}
            >
              {isPrivate
                ? __("Add/Select Private Video", "presto-player")
                : __("Add/Select Video", "presto-player")}
            </Button>
          );
        }}
      />
    );

    if (mediaUpload) {
      const content = (
        <>
          {mediaLibraryButton}
          {!!allowURLs && renderUrlSelectionUI()}
          {renderCancelLink()}
        </>
      );
      return renderPlaceholder(content);
    }

    return renderPlaceholder(mediaLibraryButton);
  };

  return (
    <MediaUploadCheck fallback={renderPlaceholder(renderUrlSelectionUI())}>
      {renderMediaUploadChecked()}
    </MediaUploadCheck>
  );
}

/**
 * @see https://github.com/WordPress/gutenberg/blob/master/packages/block-editor/src/components/media-placeholder/README.md
 */
export default withFilters("editor.MediaPlaceholder")(MediaPlaceholder);

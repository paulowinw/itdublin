import { __ } from "@wordpress/i18n";
import { Button, BaseControl } from "@wordpress/components";
import { MediaUpload } from "@wordpress/media-utils";
import classNames from "classnames";

export default ({
  option,
  label,
  help,
  allowedTypes,
  value,
  className,
  maxWidth,
  onSelect,
}) => {
  return (
    <div
      className={classNames(
        className,
        "presto-settings__setting is-media-control"
      )}
    >
      <BaseControl className="editor-video-poster-control">
        <BaseControl.VisualLabel>{label}</BaseControl.VisualLabel>
        {value && (
          <BaseControl>
            <img
              style={{
                maxWidth,
                border: "1px solid #dcdcdc",
              }}
              src={value}
            />
          </BaseControl>
        )}
        <br />
        <MediaUpload
          title={help}
          onSelect={onSelect}
          allowedTypes={allowedTypes}
          render={({ open }) => (
            <Button
              isSecondary
              onClick={open}
              className={!value ? "button-select" : "button-replace"}
            >
              {!value
                ? __("Select", "presto-player")
                : __("Replace", "presto-player")}
            </Button>
          )}
        />{" "}
        <p id={`video-block__logo-image-description-${option?.id}`} hidden>
          {value
            ? sprintf(
                /* translators: %s: poster image URL. */
                __("The current logo image url is %s", "presto-player"),
                value
              )
            : __("There is no logo image currently selected", "presto-player")}
        </p>
        {!!value && (
          <Button onClick={() => onSelect("")} isTertiary>
            {__("Remove", "presto-player")}
          </Button>
        )}
      </BaseControl>
      <br />
    </div>
  );
};

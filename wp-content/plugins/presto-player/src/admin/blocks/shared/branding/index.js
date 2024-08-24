/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const {
  ColorPicker,
  Button,
  withNotices,
  BaseControl,
  RangeControl,
} = wp.components;
const { MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { useInstanceId } = wp.compose;
const { useState } = wp.element;
const { dispatch, useSelect } = wp.data;
import ProBadge from "@/admin/blocks/shared/components/ProBadge";

const VIDEO_LOGO_ALLOWED_MEDIA_TYPES = ["image"];

function PlayerSettings({ setAttributes, attributes, type }) {
  const instanceId = useInstanceId(PlayerSettings);
  const [pickerRender, setPickerRender] = useState(1);

  // get branding options
  const branding = useSelect((select) => {
    return select("presto-player/player").branding();
  });
  // save options
  const saveOptions = async () => {
    await dispatch("presto-player/player").saveOptions({
      branding,
    });
    wp.data.dispatch("core/notices").createNotice(
      "success", // Can be one of: success, info, warning, error.
      "Player branding saved.", // Text string to display.
      {
        type: "snackbar",
        isDismissible: true, // Whether the user can dismiss the notice.
        // Any actions the user can perform.
      }
    );
  };

  function onSelectLogo(image) {
    dispatch("presto-player/player").updateBranding("logo", image.url);
  }

  function onRemoveLogo() {
    dispatch("presto-player/player").updateBranding("logo", "");
  }

  return (
    <div className="presto-player__panel--branding">
      {type !== "audio" && (
        <p>
          {__(
            "Here you can select the global player branding This will apply to all players on the site.",
            "presto-player"
          )}
        </p>
      )}

      <MediaUploadCheck>
        <BaseControl className="editor-video-poster-control">
          {type !== "audio" && (
            <>
              <BaseControl.VisualLabel>
                <p>
                  {__("Logo Overlay", "presto-player")}{" "}
                  {!prestoPlayer?.isPremium && <ProBadge />}
                </p>
              </BaseControl.VisualLabel>
              <MediaUpload
                title={__("Select logo overlay image", "presto-player")}
                onSelect={onSelectLogo}
                allowedTypes={VIDEO_LOGO_ALLOWED_MEDIA_TYPES}
                render={({ open }) => (
                  <Button
                    className="presto-setting__logo"
                    isSecondary
                    onClick={() => {
                      if (!prestoPlayer?.isPremium) {
                        dispatch("presto-player/player").setProModal(true);
                        return;
                      }
                      open();
                    }}
                    aria-describedby={`video-block__logo-image-description-${instanceId}`}
                  >
                    {!branding?.logo
                      ? __("Select", "presto-player")
                      : __("Replace", "presto-player")}
                  </Button>
                )}
              />
            </>
          )}

          <p id={`video-block__logo-image-description-${instanceId}`} hidden>
            {branding?.logo
              ? sprintf(
                  /* translators: %s: poster image URL. */
                  __("The current logo image url is %s", "presto-player"),
                  branding?.logo
                )
              : __(
                  "There is no logo image currently selected",
                  "presto-player"
                )}
          </p>
          {!!branding?.logo && (
            <Button onClick={onRemoveLogo} isTertiary>
              {__("Remove", "presto-player")}
            </Button>
          )}
        </BaseControl>
      </MediaUploadCheck>

      {!!branding?.logo && (
        <RangeControl
          label={__("Logo Max Width", "presto-player")}
          value={branding?.logo_width || 150}
          onChange={(width) =>
            dispatch("presto-player/player").updateBranding("logo_width", width)
          }
          min={1}
          max={400}
        />
      )}

      <ColorPicker
        color={branding?.color}
        onChangeComplete={(value) => {
          dispatch("presto-player/player").updateBranding("color", value.hex);
        }}
        key={pickerRender}
        disableAlpha
      />

      {branding?.color && (
        <BaseControl>
          <Button
            isSecondary
            onClick={() => {
              dispatch("presto-player/player").updateBranding(
                "color",
                prestoPlayer?.defaults?.color || "#00b3ff"
              );
              setPickerRender(pickerRender + 1);
            }}
          >
            {__("Reset Color", "presto-player")}
          </Button>
        </BaseControl>
      )}

      <Button isPrimary onClick={saveOptions}>
        {__("Save Branding", "presto-player")}
      </Button>
    </div>
  );
}

export default withNotices(PlayerSettings);

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const {
  ToggleControl,
  SelectControl,
  BaseControl,
  Button,
  PanelRow,
  Icon,
  Flex,
} = wp.components;
const { dispatch } = wp.data;
import { isHLS } from "@/shared/util";

const { MediaUpload, MediaUploadCheck } = wp.blockEditor;
import ProBadge from "@/admin/blocks/shared/components/ProBadge";
import MutedPreviewOptions from "./MutedPreviewOptions";

const VIDEO_POSTER_ALLOWED_MEDIA_TYPES = ["image"];

const { useInstanceId } = wp.compose;

const VideoSettings = ({ setAttributes, attributes }) => {
  const {
    mutedPreview,
    autoplay,
    playsInline,
    preload,
    poster,
    mutedOverlay,
  } = attributes;

  const instanceId = useInstanceId(VideoSettings);

  const videoPosterDescription = `video-block__poster-image-description-${instanceId}`;

  const getAutoplayHelp = (checked) => {
    return checked
      ? __(
          "Note: Autoplaying videos may cause usability issues for some visitors.",
          "presto-player"
        )
      : null;
  };

  const posterRecommended = () => {
    // if is hls and we've selected metadata or none, recommend a poster
    if (
      attributes?.src &&
      isHLS(attributes?.src) &&
      ["metadata", "none"].includes(preload)
    ) {
      return true;
    }

    return preload === "none" && !poster;
  };

  const toggleAttribute = (attribute) => {
    return (newValue) => {
      setAttributes({ [attribute]: newValue });
    };
  };

  // handle poster select
  function onSelectPoster(image) {
    setAttributes({ poster: image.url });
  }

  function onRemovePoster() {
    setAttributes({ poster: "" });
  }

  const mutedPreviewControls = () => {
    return (
      <>
        <ToggleControl
          label={
            <>
              {__("Muted Autoplay Preview", "presto-player")}{" "}
              {!prestoPlayer?.isPremium && <ProBadge />}
            </>
          }
          onChange={(value) => {
            if (!prestoPlayer?.isPremium) {
              dispatch("presto-player/player").setProModal(true);
              return;
            }
            setAttributes({
              mutedPreview: {
                ...mutedPreview,
                ...{ enabled: value },
              },
            });
          }}
          checked={mutedPreview?.enabled}
          className="presto-setting__mutedPreview"
          help={__("Shows a muted preview of the video.", "presto-player")}
        />
        {!!mutedPreview?.enabled && !attributes?.video_id && (
          <PanelRow>
            <ToggleControl
              label={__("Muted Preview Captions", "presto-player")}
              onChange={(value) => {
                setAttributes({
                  mutedPreview: {
                    ...mutedPreview,
                    ...{ captions: value },
                  },
                });
              }}
              checked={mutedPreview?.captions}
              className="presto-setting__mutedPreviewCaptions"
              help={__("Play captions during muted autoplay", "presto-player")}
            />
          </PanelRow>
        )}

        {!!mutedPreview.enabled && (
          <PanelRow>
            <ToggleControl
              label={
                <>
                  {__("Muted Preview Overlay", "presto-player")}{" "}
                  {!prestoPlayer?.isPremium && <ProBadge />}
                </>
              }
              onChange={(value) => {
                if (!prestoPlayer?.isPremium) {
                  dispatch("presto-player/player").setProModal(true);
                  return;
                }
                setAttributes({
                  mutedOverlay: {
                    ...mutedOverlay,
                    ...{ enabled: value },
                  },
                });
              }}
              checked={mutedOverlay?.enabled}
              className="presto-setting__mutedOverlay"
              help={__(
                "Show an image over the top of the video either before or after the video.",
                "presto-player"
              )}
            />
          </PanelRow>
        )}
        {mutedOverlay?.enabled && mutedPreview?.enabled && (
          <MutedPreviewOptions
            attributes={attributes}
            setAttributes={setAttributes}
          />
        )}
      </>
    );
  };

  return (
    <>
      {!autoplay && mutedPreviewControls()}

      {!mutedPreview?.enabled && (
        <ToggleControl
          label={__("Autoplay", "presto-player")}
          className="presto-setting__autoplay"
          onChange={toggleAttribute("autoplay")}
          checked={autoplay}
          help={getAutoplayHelp}
        />
      )}
      <PanelRow>
        <ToggleControl
          label={__("Play inline", "presto-player")}
          className="presto-setting__playsInline"
          data-cy={"playsInline"}
          onChange={toggleAttribute("playsInline")}
          checked={playsInline}
          help={__(
            "On mobile browsers, play the video on the page instead of opening it up fullscreen.",
            "presto-player"
          )}
        />
      </PanelRow>
      {!attributes?.video_id && (
        <PanelRow>
          <SelectControl
            label={
              <Flex>
                <div>{__("Performance Preference", "presto-player")}</div>
                <a
                  href="https://prestoplayer.com/docs/performance-preferences-explained"
                  target="_blank"
                  style={{ textDecoration: "none" }}
                >
                  <Icon icon="editor-help" />
                </a>
              </Flex>
            }
            className="presto-setting__preload"
            value={preload}
            onChange={(value) => setAttributes({ preload: value })}
            help={
              posterRecommended() &&
              __(
                "A poster image is recommended for this setting.",
                "presto-player"
              )
            }
            options={[
              {
                value: "auto",
                label: __("Video Playback Speed", "presto-player"),
              },
              {
                value: "metadata",
                label: __("Page Load Speed", "presto-player"),
              },
              {
                value: "none",
                label: __("Page Load Speed (Extreme)", "presto-player"),
              },
            ]}
          />
        </PanelRow>
      )}
      <MediaUploadCheck>
        <BaseControl className="editor-video-poster-control">
          <BaseControl.VisualLabel>
            <p>{__("Poster image", "presto-player")}</p>
          </BaseControl.VisualLabel>
          <MediaUpload
            title={__("Select poster image", "presto-player")}
            onSelect={onSelectPoster}
            allowedTypes={VIDEO_POSTER_ALLOWED_MEDIA_TYPES}
            render={({ open }) => (
              <Button
                className="presto-setting__poster"
                isPrimary
                onClick={open}
                aria-describedby={videoPosterDescription}
              >
                {!poster
                  ? __("Select", "presto-player")
                  : __("Replace", "presto-player")}
              </Button>
            )}
          />
          <p id={videoPosterDescription} hidden>
            {poster
              ? sprintf(
                  __("The current poster image url is %s", "presto-player"),
                  poster
                )
              : __(
                  "There is no poster image currently selected",
                  "presto-player"
                )}
          </p>
          {!!poster && (
            <Button
              onClick={onRemovePoster}
              className="presto-setting__remove-poster"
              isTertiary
            >
              {__("Remove", "presto-player")}
            </Button>
          )}
        </BaseControl>
      </MediaUploadCheck>
    </>
  );
};

export default VideoSettings;

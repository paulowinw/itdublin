import { __ } from "@wordpress/i18n";
import VideoBranding from "@/admin/blocks/shared/branding";
import {
  Button,
  PanelBody,
  BaseControl,
  ToggleControl,
  PanelRow,
  SelectControl,
  Flex,
  Icon,
  TextControl,
  FlexBlock,
} from "@wordpress/components";
import { useInstanceId } from "@wordpress/compose";
import { MediaUpload, MediaUploadCheck } from "@wordpress/block-editor";
import { useSelect } from "@wordpress/data";
import { store as coreStore } from "@wordpress/core-data";
import VideoChapters from "@/admin/blocks/shared/chapters";
import ProBadge from "@/admin/blocks/shared/components/ProBadge";
import AudioPresets from "@/admin/blocks/shared/audioPresets";

const AUDIO_POSTER_ALLOWED_MEDIA_TYPES = ["image"];

function AudioBlockInspectorControl({ attributes, setAttributes }) {
  const instanceId = useInstanceId(AudioBlockInspectorControl);
  const audioPosterDescription = `audio-block__poster-image-description-${instanceId}`;

  const userCanReadSettings = useSelect((select) =>
    select(coreStore).canUser("read", "settings")
  );

  const { autoplay, poster, preload, title } = attributes;

  function onSelectPoster(image) {
    setAttributes({ poster: image.url });
  }
  function onRemovePoster() {
    setAttributes({ poster: "" });
  }
  return (
    <>
      <PanelBody
        title={<>{__("Title", "presto-player")} </>}
        initialOpen={true}
      >
        <FlexBlock>
          <TextControl
            className={"presto-player__caption--title"}
            placeholder={__("Title", "presto-player")}
            value={title || ""}
            onChange={(title) => setAttributes({ title: title })}
            autoComplete="off"
          />
        </FlexBlock>
      </PanelBody>

      {/* Chapters  */}
      <PanelBody
        title={
          <>
            {__("Chapters", "presto-player")}{" "}
            {!prestoPlayer?.isPremium && <ProBadge />}
          </>
        }
        initialOpen={prestoPlayer?.isPremium}
      >
        <VideoChapters setAttributes={setAttributes} attributes={attributes} />
      </PanelBody>

      <PanelBody title={<>{__("Audio Settings", "presto-player")} </>}>
        {/* Autoplay Settings  */}
        <ToggleControl
          label={<>{__("Autoplay", "presto-player")} </>}
          checked={autoplay}
          onChange={(autoplay) => {
            setAttributes({ autoplay });
          }}
        />

        {/* Performance Preference */}
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
            options={[
              {
                value: "auto",
                label: __("Audio Playback Speed", "presto-player"),
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

        {/* Poster Image */}
        <MediaUploadCheck>
          <BaseControl className="editor-video-poster-control">
            <BaseControl.VisualLabel>
              <p>{__("Poster image", "presto-player")}</p>
            </BaseControl.VisualLabel>
            <MediaUpload
              title={__("Select poster image", "presto-player")}
              onSelect={onSelectPoster}
              allowedTypes={AUDIO_POSTER_ALLOWED_MEDIA_TYPES}
              render={({ open }) => (
                <Button
                  className="presto-setting__poster"
                  isPrimary
                  onClick={open}
                  aria-describedby={audioPosterDescription}
                >
                  {!poster
                    ? __("Select", "presto-player")
                    : __("Replace", "presto-player")}
                </Button>
              )}
            />
            <p id={audioPosterDescription} hidden>
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
      </PanelBody>

      {/* Audio Presets */}
      <PanelBody title={__("Audio Preset", "presto-player")}>
        <AudioPresets setAttributes={setAttributes} attributes={attributes} />
      </PanelBody>

      {/* Global Branding  */}
      {!!userCanReadSettings && (
        <PanelBody
          title={<>{__("Global Player Branding", "presto-player")}</>}
          initialOpen={false}
        >
          <VideoBranding
            setAttributes={setAttributes}
            attributes={attributes}
            type="audio"
          />
        </PanelBody>
      )}
    </>
  );
}

export default AudioBlockInspectorControl;

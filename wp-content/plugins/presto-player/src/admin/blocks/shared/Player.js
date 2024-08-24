import {
  PrestoPlayer,
  PrestoSearchBarUi,
} from "@presto-player/components-react";
import { getProvider } from "../util";
import { useRef, useEffect } from "@wordpress/element";
import { useSelect } from "@wordpress/data";
import { convertHex } from "../../../shared/util";

export default (props) => {
  const {
    src,
    classes,
    preset,
    branding,
    attributes,
    adminPreview,
    currentTime,
    preload = "metadata",
    overlays,
    type,
  } = props;

  const ref = useRef();
  const {
    previewThumbnail,
    preview,
    chapters,
    poster,
    mutedOverlay,
    mutedPreview,
    title,
  } = attributes;

  const { youtube, playerCSS } = useSelect((select) => {
    return {
      youtube: select("presto-player/player")?.youtube(),
      playerCSS: select("presto-player/player")?.playerCSS(),
    };
  });

  useEffect(() => {
    ref.current.src = src;
    ref.current["data-css"] = playerCSS;
    ref.current.classes = classes;
    ref.current.currentTime = currentTime;
    ref.current.overlays = overlays;
    ref.current.isAdmin = true;
    ref.current.preload = preload;
    ref.current.preset = preset;
    ref.current.bunny = {
      thumbnail: previewThumbnail,
      preview,
    };
    ref.current.youtube = {
      channelId: youtube?.channel_id,
    };
    ref.current.tracks = [
      ...(!!preset?.captions
        ? [
            {
              kind: "captions",
              label: "English",
              srclang: "en",
              src: "/path/to/captions.en.vtt",
              default: true,
            },
          ]
        : []),
    ];
    ref.current.branding = branding;
    ref.current.chapters = chapters;
    ref.current.blockAttributes = attributes;
    ref.current.poster = poster;
    ref.current.provider = type === "audio" ? "audio" : getProvider(src);
    ref.current.mediaTitle = title;
  }, [
    src,
    classes,
    preset,
    branding,
    attributes,
    adminPreview,
    currentTime,
    preload,
    overlays,
    type,
  ]);

  const mutedOverlayContent = () => {
    return (
      <div
        className="presto-player__overlay is-image"
        style={{
          position: "absolute",
          width: `${mutedOverlay?.width || 100}%`,
          left: `${(mutedOverlay?.focalPoint?.x || 0.5) * 100}%`,
          top: `${(mutedOverlay?.focalPoint?.y || 0.5) * 100}%`,
        }}
      >
        <img
          src={mutedOverlay?.src}
          style={{
            transform: "translateX(-50%) translateY(-50%)",
          }}
        />
      </div>
    );
  };

  return (
    <div
      className={"wp-block-video presto-block-video"}
      style={
        type === "audio"
          ? {
              "--presto-player-border-radius": `${preset?.border_radius}px`,
              ...(preset?.background_color
                ? {
                    "--plyr-audio-controls-background": preset.background_color,
                  }
                : { "--plyr-audio-controls-background": branding?.color }),
              ...(preset?.control_color
                ? {
                    "--plyr-audio-control-color": preset.control_color,
                    "--plyr-range-thumb-background": preset.control_color,
                    "--plyr-range-fill-background": preset.control_color,
                    "--plyr-audio-progress-buffered-background": convertHex(
                      preset.control_color || branding?.color || "#00b3ff",
                      0.5
                    ),
                  }
                : {
                    "--plyr-audio-control-color": "#ffffff",
                    "--plyr-range-thumb-background": "#ffffff",
                    "--plyr-range-fill-background": "#ffffff",
                  }),
              "--plyr-range-thumb-shadow": `0px`,
            }
          : {
              "--presto-player-border-radius": `${preset?.border_radius}px`,
              ...(preset?.caption_background
                ? { "--plyr-captions-background": preset.caption_background }
                : {}),
              ...(branding?.color
                ? { "--plyr-color-main": `var(--presto-player-highlight-color, ${branding.color})` }
                : {}),
              "--presto-player-email-border-radius": `${
                preset?.email_collection?.border_radius || 0
              }px`,
              "--presto-player-logo-width": `${branding?.logo_width || 75}px`,
            }
      }
    >
      <PrestoPlayer ref={ref}>
        <div slot="player-end">
          {!!preset.search?.enabled && (
            <PrestoSearchBarUi
              style={{
                position: "absolute",
                top: "15px",
                right: "23px",
                zIndex: 1,
              }}
              placeholder={preset.search?.placeholder}
            />
          )}
          {mutedPreview?.enabled &&
            mutedOverlay?.enabled &&
            mutedOverlayContent()}
          {adminPreview}
        </div>
      </PrestoPlayer>
    </div>
  );
};

import { __ } from "@wordpress/i18n";
import { TextControl } from "@wordpress/components";
import { useEntityProp } from "@wordpress/core-data";

export default () => {
  const [stream, setStream] = useEntityProp(
    "root",
    "site",
    "presto_player_bunny_stream_public"
  );
  const updateStream = (data) => {
    setStream({
      ...(stream || {}),
      ...data,
    });
  };

  const { video_library_api_key, pull_zone_url, video_library_id } =
    stream || {};

  return (
    <>
      <TextControl
        label={__("Public Stream Library ID", "presto-player")}
        help={__("The ID of the video library to use.", "presto-player")}
        value={video_library_id}
        onChange={(video_library_id) => updateStream({ video_library_id })}
      />

      <TextControl
        label={__("Public Stream Library API Key", "presto-player")}
        help={__(
          "The API key for the above video library for read/write access.",
          "presto-player"
        )}
        value={video_library_api_key}
        onChange={(video_library_api_key) =>
          updateStream({ video_library_api_key })
        }
      />

      <TextControl
        label={__("Public Stream CDN Hostname", "presto-player")}
        help={__(
          "The public cdn hostname for the video library.",
          "presto-player"
        )}
        value={pull_zone_url}
        onChange={(pull_zone_url) => updateStream({ pull_zone_url })}
      />
    </>
  );
};

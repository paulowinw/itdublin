const { withSelect } = wp.data;

export default function (props) {
  return withSelect((select) => {
    return {
      branding: select("presto-player/player").branding(),
      loading: select("presto-player/player").isResolving("getAudioPresets"),
      presets: select("presto-player/player").getAudioPresets(),
      defaultPreset: select("presto-player/player").getDefaultAudioPreset(),
    };
  });
}

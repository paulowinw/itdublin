export function getPresets(state) {
  return state?.presetReducer || [];
}
export function getAudioPresets(state) {
  return state?.audioPresetReducer || [];
}
export function getPreset(state, id) {
  if (state?.presetReducer?.length) {
    return state?.presetReducer.find((item) => item.id === id);
  }
  return [];
}
export function getReusableVideos(state) {
  return state?.videosReducer || [];
}
export function getReusableVideo(state, id) {
  return state?.videosReducer?.videos?.find((video) => video?.id === id) || [];
}
export function getDefaultPreset(state) {
  const default_preset = state?.presetSettingsReducer?.default_player_preset;

  let preset =
    default_preset &&
    (state?.presetReducer || []).find((preset) => {
      return preset.id && preset.id === default_preset;
    });

  if (!preset) {
    preset = (state?.presetReducer || []).find((preset) => {
      return preset.slug == "default";
    });
  }
  if (!preset) {
    preset = (state?.presetReducer || [])[0];
  }
  return preset;
}
export function getDefaultAudioPreset(state) {
  const default_preset = state?.audioPresetSettingsReducer?.default_player_preset;

  let preset =
    default_preset &&
    (state?.audioPresetReducer || []).find((preset) => {
      return preset.id && preset.id === default_preset;
    });

  if (!preset) {
    preset = (state?.audioPresetReducer || []).find((preset) => {
      return preset.slug == "default";
    });
  }
  if (!preset) {
    preset = (state?.audioPresetReducer || [])[0];
  }
  return preset;
}
export function presetsLoading(state) {
  return !!state?.presetLoadingReducer;
}
export function branding(state) {
  return state?.brandingReducer;
}
export function playerCSS(state) {
  return state?.brandingReducer?.player_css;
}
export function youtube(state) {
  return state?.youtubeReducer;
}
export function proModal(state) {
  return state?.proModalReducer;
}

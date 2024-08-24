export function setPresetLoading(value) {
  return {
    type: "SET_PRESET_LOADING",
    value,
  };
}

export function setVideosLoading(value) {
  return {
    type: "SET_VIDEOS_LOADING",
    value,
  };
}

export function setProModal(value) {
  return {
    type: "SET_PRO_MODAL",
    value,
  };
}

/**
 * Videos
 */
export function setVideos(value) {
  return {
    type: "SET_VIDEOS",
    value,
  };
}
export function updateVideos(value) {
  return {
    type: "UPDATE_VIDEOS",
    value,
  };
}
export function appendVideos(value) {
  return {
    type: "APPEND_VIDEOS",
    value,
  };
}
export function addVideo(value) {
  return {
    type: "ADD_VIDEO",
    value,
  };
}

/**
 * Presets
 */
export function setPresets(value) {
  return {
    type: "SET_PRESET",
    value,
  };
}
export function addPreset(value) {
  return {
    type: "ADD_PRESET",
    value,
  };
}
export function updatePreset(value) {
  return {
    type: "UPDATE_PRESET",
    value,
  };
}
export function removePreset(value) {
  return {
    type: "REMOVE_PRESET",
    value,
  };
}

/**
 * Audio Presets
 */

export function setAudioPresets(value) {
  return {
    type: "SET_AUDIO_PRESET",
    value,
  };
}

export function addAudioPreset(value) {
  return {
    type: "ADD_AUDIO_PRESET",
    value,
  };
}
export function updateAudioPreset(value) {
  return {
    type: "UPDATE_AUDIO_PRESET",
    value,
  };
}
export function removeAudioPreset(value) {
  return {
    type: "REMOVE_AUDIO_PRESET",
    value,
  };
}

/**
 * Branding
 */
export function setBranding(value) {
  return {
    type: "SET_BRANDING",
    value,
  };
}
export function updateBranding(name, value) {
  return {
    type: "UPDATE_BRANDING",
    name,
    value,
  };
}

/**
 * Save provided settings
 */
export function saveOptions(args) {
  return {
    type: "SAVE_OPTIONS",
    ...args,
  };
}

/**
 * FetchOptions
 */
export function fetchOptions() {
  return {
    type: "FETCH_OPTIONS",
  };
}

export function fetchFromAPI(path) {
  return {
    type: "FETCH_FROM_API",
    path,
  };
}

export function fetchFromWPAPI(path, args) {
  return {
    type: "FETCH_FROM_WP_API",
    path,
    args,
  };
}

export function setYoutube(value) {
  return {
    type: "SET_YOUTUBE",
    value,
  };
}

export function updateYoutube(name, value) {
  return {
    type: "UPDATE_YOUTUBE",
    name,
    value,
  };
}

export function setPresetSettings(value) {
  return {
    type: "SET_PRESET_SETTINGS",
    value,
  };
}

export function setPresetAudioSettings(value) {
  return {
    type: "SET_PRESET_AUDIO_SETTINGS",
    value,
  };
}
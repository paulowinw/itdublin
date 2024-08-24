import { combineReducers, dispatch } from '@wordpress/data';
import apiFetch from  '@wordpress/api-fetch';
import { pick } from 'lodash';

/**
 * Presets reducer
 *
 * @param {array} state
 * @param {object} action
 */
const presetReducer = (state = [], action) => {
  switch (action.type) {
    case "SET_PRESET":
      return action.value;
    case "ADD_PRESET":
      return [...state, ...[action.value]];
    case "UPDATE_PRESET":
      return state.map((item, index) => {
        if (item.id !== action.value?.id) {
          return item;
        }
        return {
          ...item,
          ...action.value,
        };
      });
    case "REMOVE_PRESET":
      return state.filter((item) => {
        return item !== action.value;
      });
  }
  return state;
};

/**
 * Presets reducer
 *
 * @param {array} state
 * @param {object} action
 */
 const audioPresetReducer = (state = [], action) => {
  switch (action.type) {
    case "SET_AUDIO_PRESET":
      return action.value;
    case "SET_AUDIO_PRESET":
        return action.value;
    case "ADD_AUDIO_PRESET":
      return [...state, ...[action.value]];
    case "UPDATE_AUDIO_PRESET":
      return state.map((item, index) => {
        if (item.id !== action.value?.id) {
          return item;
        }
        return {
          ...item,
          ...action.value,
        };
      });
    case "REMOVE_AUDIO_PRESET":
      return state.filter((item) => {
        return item !== action.value;
      });
  }
  return state;
};

/**
 * For preset loading
 *
 * @param {boolean} state
 * @param {object} action
 */
const presetLoadingReducer = (state = true, action) => {
  switch (action.type) {
    case "SET_PRESET_LOADING":
      return action.value;
  }
};

/**
 * For preset loading
 *
 * @param {boolean} state
 * @param {object} action
 */
const videosLoadingReducer = (state = true, action) => {
  switch (action.type) {
    case "SET_VIDEOS_LOADING":
      return action.value;
  }
};

const proModalReducer = (state = false, action) => {
  switch (action.type) {
    case "SET_PRO_MODAL":
      return action.value;
  }
};

const addVideo = (videos, video) => {
  // check for existing item
  const index = videos.find((e) => e.id === video.id);
  if (index) {
    return videos;
  }
  return [...videos, ...[video]];
};

const videosReducer = (
  state = {
    total: 0,
    total_pages: 0,
    videos: [],
    hasResolved: false,
    isError: false,
  },
  action
) => {
  switch (action.type) {
    case "SET_VIDEOS":
      return action.value;
    case "UPDATE_VIDEOS":
      return { ...state, ...action.value };
    case "APPEND_VIDEOS":
      let draft = state;
      (action.value || []).forEach((video) => {
        draft.videos = addVideo(draft.videos, video);
      });
      return draft;
    case "ADD_VIDEO":
      return { ...state, videos: addVideo(state.videos, action.value) };
    case "UPDATE_VIDEO":
      return {
        ...state,
        videos: state.videos.map((item, index) => {
          if (item.id !== action.value?.id) {
            return item;
          }
          return {
            ...item,
            ...action.value,
          };
        }),
      };
    case "REMOVE_VIDEO":
      return {
        ...state,
        videos: state.videos.filter((item) => {
          return item !== action.value;
        }),
      };
  }
  return state;
};

/**
 * Branding options are global and stored in settings
 * @param {object} state
 * @param {object} action
 */
const brandingReducer = (state = {}, action) => {
  switch (action.type) {
    case "SET_BRANDING":
      return action.value;
    case "UPDATE_BRANDING":
      return {
        ...state,
        [action.name]: action.value,
      };
  }
  return state;
};

/**
 * Youtube are global and stored in settings
 * @param {object} state
 * @param {object} action
 */
const youtubeReducer = (state = {}, action) => {
  switch (action.type) {
    case "SET_YOUTUBE":
      return action.value;
    case "UPDATE_YOUTUBE":
      return {
        ...state,
        [action.name]: action.value,
      };
  }
  return state;
};

/**
 * General are global and stored in settings
 * @param {object} state
 * @param {object} action
 */
const presetSettingsReducer = (state = {}, action) => {
  switch (action.type) {
    case "SET_PRESET_SETTINGS":
      return action.value;
  }
  return state;
};
const audioPresetSettingsReducer = (state = {}, action) => {
  switch (action.type) {
    case "SET_PRESET_AUDIO_SETTINGS":
      return action.value;
  }
  return state;
};
/**
 * For fetching the options
 *
 * @param {object} state
 * @param {object} action
 */
const optionsApi = (state, action) => {
  switch (action.type) {
    /**
     * Fetch our options
     */
    case "FETCH_OPTIONS":
      return apiFetch({
        path: "/presto-player/v1/settings/",
      }).then((settings) => {
        dispatch("presto-player/player").setBranding(
          settings.presto_player_branding
        );
        dispatch("presto-player/player").setYoutube(
          settings.presto_player_youtube
        );
        dispatch("presto-player/player").setPresetSettings(
          settings.presto_player_presets
        );
        dispatch("presto-player/player").setPresetAudioSettings(
          settings.presto_player_audio_presets
        );
      });

    /**
     * Persist options to db
     */
    case "SAVE_OPTIONS":
      const data = {
        presto_player_branding: pick(action?.branding, [
          "logo",
          "color",
          "logo_width",
          "player_css",
        ]),
      };

      // remove blanks
      Object.keys(data).forEach(
        (key) =>
          (data[key] == null || !Object.keys(data?.[key] || {}).length) &&
          delete data[key]
      );

      apiFetch({
        path: "/presto-player/v1/settings",
        method: "POST",
        data,
      });

      return data;
  }
};

export default combineReducers({
  presetReducer,
  audioPresetReducer,
  presetLoadingReducer,
  videosLoadingReducer,
  videosReducer,
  proModalReducer,
  brandingReducer,
  youtubeReducer,
  presetSettingsReducer,
  optionsApi,
  audioPresetSettingsReducer
});

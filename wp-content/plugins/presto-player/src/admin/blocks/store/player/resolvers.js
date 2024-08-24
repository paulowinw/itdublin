import * as actions from "./actions";

export default {
  *getPresets() {
    const presets = yield actions.fetchFromAPI("preset");
    return actions.setPresets(presets);
  },
  *getAudioPresets(){
    const presets = yield actions.fetchFromAPI("audio-preset");
    return actions.setAudioPresets(presets);
  },
  *getReusableVideo(id) {
    const path = `presto-videos/${id}`;
    const preset = yield actions.fetchFromWPAPI(path, {});
    return actions.addVideo(preset?.data || {});
  },
};

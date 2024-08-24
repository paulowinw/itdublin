const { __ } = wp.i18n;
const { dispatch, useSelect } = wp.data;
import apiFetch from "@/shared/services/fetch-wp";
const root = prestoPlayer.root + prestoPlayer.wpVersionString;

// raw settings
export function getSettings() {
  return useSelect((select) => {
    return select("presto-player/settings").settings();
  });
}

export function filterSettings(settings) {
  const keys = [
    "presto_player_analytics",
    "presto_player_presets",
    "presto_player_branding",
    "presto_player_bunny_storage_zones",
    "presto_player_bunny_pull_zones",
    "presto_player_bunny_stream",
    "presto_player_bunny_stream_public",
    "presto_player_bunny_stream_private",
    "presto_player_google_analytics",
    "presto_player_general",
    "presto_player_uninstall",
    "presto_player_performance",
    "presto_player_mailchimp",
    "presto_player_mailerlite",
    "presto_player_youtube",
    "presto_player_activecampaign",
    "presto_player_fluentcrm",
    "presto_player_audio_presets",
    "presto_player_instant_video_width",
    "presto_player_media_hub_sync_default"
  ];

  let settingsToSet = {};
  // important - if settings are returned as null (invalid) we need to reset
  keys.forEach((key) => {
    settingsToSet[key] = settings[key] ? settings[key] : {};
  });
  return settingsToSet;
}

// get settings and set in store
export function fetchSettings() {
  return apiFetch({
    url: `${root}settings/`,
  }).then((settings) => {
    dispatch("presto-player/settings").setSettings(filterSettings(settings));
    return settings;
  });
}

export function saveSettings(settings) {
  dispatch("presto-player/settings").setSaving(true);
  apiFetch({
    url: `${root}settings/`,
    method: "POST",
    data: settings,
  })
    .then((settings) => {
      dispatch("presto-player/settings").setSettings(filterSettings(settings));
      dispatch("presto-player/settings").addNotice({
        content: __("Settings saved.", "presto-player"),
      });
    })
    .catch((e) => {
      dispatch("presto-player/settings").addNotice({
        content: e.message ? e.message : "Something went wrong.",
        className: "is-snackbar-error",
      });
    })
    .finally(() => {
      dispatch("presto-player/settings").setSaving(false);
    });
}

// get an option with a fallback
export function get_option(name, fallback) {
  const settings = getSettings();
  return settings?.[name] ? settings[name] : fallback;
}

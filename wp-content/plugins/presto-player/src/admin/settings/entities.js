import { store as coreStore } from "@wordpress/core-data";
import { dispatch } from "@wordpress/data";
import { __ } from "@wordpress/i18n";

dispatch(coreStore).addEntities([
  {
    name: "preset",
    kind: "presto-player",
    label: __("Presets", "presto-player"),
    baseURL: "presto-player/v1/preset",
    baseURLParams: { context: "edit" },
  },
  {
    name: "audio-preset",
    kind: "presto-player",
    label: __("Audio Presets", "presto-player"),
    baseURL: "presto-player/v1/audio-preset",
    baseURLParams: { context: "edit" },
  },
  {
    name: "webhook",
    kind: "presto-player",
    label: __("Webhook", "presto-player"),
    baseURL: "presto-player/v1/webhook",
    baseURLParams: { context: "edit" },
  },
]);

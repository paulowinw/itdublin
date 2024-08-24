import { __ } from "@wordpress/i18n";
import edit from "./edit";
export const name = "presto-player/audio";

export const options = {
  category: "presto",

  attributes: {
    src: {
      type: "string",
    },
    autoplay: {
      type: Boolean,
      default: false,
    },
    poster: {
      type: "string",
    },
    chapters: {
      type: "array",
      default: [],
    },
    preload: {
      type: "string",
    },
    title: {
      type: "string",
    },
    tracks: {
      type: "array",
      items: {
        type: "object",
      },
      default: [],
    },
    preset: {
      type: Number,
    },
    id: {
      type: Number,
    },
    attachment_id: {
      type: Number,
    },
    src: {
      type: String,
    },
    visibility: {
      type: String,
      default: "public",
    },
  },

  title: __("Presto Audio", "presto-player"),

  description: __("A audio player wrapper.", "presto-player"),

  variations: [
    {
      name: "public",
      isDefault: true,
      title: __("Presto Audio", "presto-player"),
      description: __("Presto self-hosted audio block.", "presto-player"),
      attributes: { visibility: "public" },
    },
    ...(prestoPlayer?.isPremium
      ? [
          {
            name: "private",
            title: __("Presto Private Audio", "presto-player"),
            icon: (
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
                className="presto-block-icon"
              >
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
            ),
            description: __(
              "Private self-hosted audio block.",
              "presto-player"
            ),
            attributes: { visibility: "private" },
          },
        ]
      : []),
  ],

  icon: (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      width="24"
      height="24"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
      className="presto-block-icon"
    >
      <path
        strokeLinecap="round"
        strokeLinejoin="round"
        strokeWidth={2}
        d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"
      />
    </svg>
  ),
  edit,
};

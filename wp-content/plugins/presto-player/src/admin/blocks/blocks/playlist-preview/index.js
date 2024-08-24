import { __ } from "@wordpress/i18n";

import edit from "./edit";

/**
 * Block Name
 */
export const name = "presto-player/playlist-preview";

/**
 * Block Options
 */
export const options = {
  apiVersion: "2",

  attributes: {
    lock: {
      type: Object,
      default: {
        remove: true,
        move: true,
      },
    },
  },

  category: "presto",

  supports: {
    align: true,
  },

  parent: ["presto-player/playlist"],

  title: __("Playlist Preview", "presto-player"),

  description: __(
    "Presto Playlist Preview Block - Shows the preview of the playlist item.",
    "presto-player"
  ),

  edit,

  icon: (
    <svg
      width="19"
      height="14"
      className="playlist-block-icon"
      viewBox="0 0 19 14"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path
        d="M0 0V2H11V0H0ZM0 4V6H11V4H0ZM17 4.1C16.9 4.1 16.7 4.2 16.6 4.3L15.6 5.3L17.7 7.4L18.7 6.4C18.9 6.2 18.9 5.8 18.7 5.6L17.4 4.3C17.3 4.2 17.2 4.1 17 4.1ZM15.1 5.9L9 11.9V14H11.1L17.2 7.9L15.1 5.9ZM0 8V10H7V8H0Z"
        fill="currentColor"
      />
    </svg>
  ),
};

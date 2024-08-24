import { InnerBlocks } from "@wordpress/block-editor";
import { __ } from "@wordpress/i18n";

import edit from "./edit";

/**
 * Block Name
 */
export const name = "presto-player/playlist-list";

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
        move: false,
      }
    }
  },

  category: "presto",

  supports: {
    align: true,
  },

  save: function () {
    return <InnerBlocks.Content />;
  },

  parent: ["presto-player/playlist-block"],

  title: __("Playlist List", "presto-player"),

  description: __("Presto Playlist List Block - Holds the Playlist List Items.", "presto-player"),

  edit,

  icon: <svg width="20" height="14" className="playlist-block-icon" viewBox="0 0 20 14" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M11 4H0V6H11V4ZM11 0H0V2H11V0ZM0 10H7V8H0V10ZM18.5 5.5L20 7L13 14L8.5 9.5L10 8L13 11L18.5 5.5Z" fill="currentColor"/>
        </svg>
};

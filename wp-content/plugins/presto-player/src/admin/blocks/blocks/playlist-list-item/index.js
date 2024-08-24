// In your application's entrypoint
import { __ } from "@wordpress/i18n";
import edit from "./edit";
import attributes from "./attributes";

/**
 * Block Name
 */
export const name = "presto-player/playlist-list-item";

/**
 * Block Options
 */
export const options = {
  attributes,
  category: "presto",
  parent: ["presto-player/playlist-list"],
  title: __("Playlist Item", "presto-player"),
  description: __("Playlist Item", "presto-player"),
  usesContext: ["presto-player/playlist-media-id"],
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
        d="M0 10H7V8H0M15 8V4H13V8H9V10H13V14H15V10H19V8M11 0H0V2H11M11 4H0V6H11V4Z"
        fill="currentColor"
      />
    </svg>
  ),
};

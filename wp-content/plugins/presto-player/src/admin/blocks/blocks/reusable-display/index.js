import { __ } from "@wordpress/i18n";
import edit from "./edit";
import save from "./save";
import { select } from "@wordpress/data";
import { store as coreStore } from "@wordpress/core-data";
import deprecated from "./deprecated";

/**
 * Block Name
 */
export const name = "presto-player/reusable-display";

/**
 * Block Options
 */
export const options = {
  title: __("Media Hub Item", "presto-player"),

  category: "presto",

  attributes: {
    id: Number,
  },

  supports: {
    inserter: false,
    reusable: false,
    html: false,
    align: true,
  },

  usesContext: ["presto-player/playlist-media-id"],
  providesContext: {
    "presto-player/playlist-media-id": "id",
  },

  icon: (
    <svg
      width="24"
      height="24"
      viewBox="0 0 24 24"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      className="presto-block-icon"
    >
      <path
        d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
      <path
        d="M10 8L16 12L10 16V8Z"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </svg>
  ),
  edit,
  save,
  __experimentalLabel: (attributes) => {
    const queryArgs = ["postType", "pp_video_block", attributes.id];
    const videoRecord = select(coreStore).getEditedEntityRecord(...queryArgs);
    return videoRecord?.title;
  },
  deprecated,
};

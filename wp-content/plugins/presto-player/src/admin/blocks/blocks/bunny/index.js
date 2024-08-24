const { __ } = wp.i18n;
import edit from "./edit";
import blockOptions from "../block-options";

/**
 * Block Name
 */
export const name = "presto-player/bunny";

/**
 * Block Options
 */
export const options = {
  ...blockOptions,

  usesContext: ["presto-player/playlist-media-id"],

  attributes: {
    ...blockOptions.attributes,
    ...{
      thumbnail: {
        type: String,
        default: "",
      },
      preview: {
        type: String,
        default: "",
      },
    },
  },

  premium: true,

  title: __("Presto Bunny.net Video", "presto-player"),

  description: __("Serve video through Bunny.net CDN.", "presto-player"),

  variations: [
    {
      name: "public",
      isDefault: true,
      title: __("Presto Bunny.net Video", "presto-player"),
      description: __(
        "Serve public video through Bunny.net CDN.",
        "presto-player"
      ),
      attributes: { visibility: "public" },
    },
    {
      name: "private",
      title: __("Presto Bunny.net Private Video", "presto-player"),
      icon: (
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          strokeWidth="2"
          strokeLinecap="round"
          strokeLinejoin="round"
          className="presto-block-icon"
        >
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
        </svg>
      ),
      description: __(
        "Serve private, secure video through Bunny.net CDN.",
        "presto-player"
      ),
      attributes: { visibility: "private" },
    },
  ],

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
      <polyline points="8 17 12 21 16 17"></polyline>
      <line x1="12" y1="12" x2="12" y2="21"></line>
      <path d="M20.88 18.09A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.29"></path>
    </svg>
  ),

  edit,
};

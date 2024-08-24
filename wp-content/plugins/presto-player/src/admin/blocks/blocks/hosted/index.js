const { __ } = wp.i18n;
import edit from "./edit";
import blockOptions from "../block-options";

/**
 * Block Name
 */
export const name = "presto-player/self-hosted";

/**
 * Block Options
 */
export const options = {
  ...blockOptions,

  usesContext: ["presto-player/playlist-media-id"],

  title: __("Presto Video", "presto-player"),

  description: __("Presto Self-hosted video block.", "presto-player"),

  variations: [
    {
      name: "public",
      isDefault: true,
      title: __("Presto Video", "presto-player"),
      description: __("Presto Self-hosted video block.", "presto-player"),
      attributes: { visibility: "public" },
    },
    ...(prestoPlayer?.isPremium
      ? [
          {
            name: "private",
            title: __("Presto Private Video", "presto-player"),
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
              "Private video self-hosted block.",
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
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
      className="presto-block-icon"
    >
      <polygon points="23 7 16 12 23 17 23 7"></polygon>
      <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
    </svg>
  ),

  edit,
};

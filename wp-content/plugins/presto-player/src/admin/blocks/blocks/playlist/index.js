/**
 * WordPress dependencies
 */
import { __ } from "@wordpress/i18n";

/**
 * Internal dependencies
 */
import edit from "./edit";
import save from "./save";
import metadata from "./block.json";

const { name } = metadata;

export { metadata, name };

export const options = {
  icon: (
    <svg
      width="19"
      height="15"
      className="playlist-block-icon"
      viewBox="0 0 19 15"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path
        d="M0 4H11V6H0V4ZM0 0H11V2H0V0ZM0 8H7V10H0V8ZM13 7V15L19 11L13 7Z"
        fill="currentColor"
      />
    </svg>
  ),
  edit,
  save,
};

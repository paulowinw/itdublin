/**
 * WordPress dependencies
 */
import { __ } from "@wordpress/i18n";
import { edit as icon } from "@wordpress/icons";

/**
 * Internal dependencies
 */
import edit from "./edit";
import save from "./save";
import metadata from "./block.json";

const { name } = metadata;

export { metadata, name };

export const options = {
  icon,
  edit,
  save,
};

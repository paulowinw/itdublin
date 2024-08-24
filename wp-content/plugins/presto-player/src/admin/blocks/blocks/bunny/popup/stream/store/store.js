/**
 * WordPress dependencies
 */
const { registerStore } = wp.data;

/**
 * Internal dependencies
 */
import reducer from "./reducer";
import * as selectors from "./selectors";
import * as actions from "./actions";

export default registerStore("presto-player/bunny-popup", {
  reducer,
  selectors,
  actions,
});

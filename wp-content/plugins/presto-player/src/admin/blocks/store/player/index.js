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
import controls from "./controls";
import resolvers from "./resolvers";

export default registerStore("presto-player/player", {
  reducer,
  selectors,
  actions,
  controls,
  resolvers,
});

import { render } from "@wordpress/element";

/**
 * Redux entities.
 */
import "./entities";

/**
 * App
 */
import App from "./app";

/**
 * styles
 */
import "./settings.scss";

/**
 * Render
 */
render(<App />, document.getElementById("presto-settings-page"));

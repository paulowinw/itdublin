const { render } = wp.element;

/**
 * App
 */
import App from "./App";

/**
 * styles
 */
import "./analytics.scss";

/**
 * Render
 */
render(<App />, document.getElementById("presto-analytics-page"));

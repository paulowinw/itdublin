/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { ToggleControl, BaseControl } = wp.components;

export default function ({ state, updateState, className }) {
  return (
    <div className={className}>
      <BaseControl>
        <h3>{__("Controls", "presto-player")}</h3>
      </BaseControl>
      <BaseControl className="presto-player__control--large-play">
        <ToggleControl
          label={__("Large Play Button", "presto-player")}
          help={__(
            "Adds a large play button over the top of the player.",
            "presto-player"
          )}
          onChange={(play) => {
            updateState({ "play-large": play });
          }}
          checked={state["play-large"]}
        />
      </BaseControl>
      <BaseControl className="presto-player__control--rewind">
        <ToggleControl
          label={__("Rewind", "presto-player")}
          help={__(
            "Adds a 10 second rewind button to the player.",
            "presto-player"
          )}
          onChange={(rewind) => {
            updateState({ rewind });
          }}
          checked={state.rewind}
        />
      </BaseControl>
      <BaseControl className="presto-player__control--play">
        <ToggleControl
          label={__("Small Play Button", "presto-player")}
          help={__(
            "Adds a small play button to the bottom of the player.",
            "presto-player"
          )}
          onChange={(play) => {
            updateState({ play });
          }}
          checked={state.play}
        />
      </BaseControl>
      <BaseControl className="presto-player__control--fast-forward">
        <ToggleControl
          label={__("Fast Forward", "presto-player")}
          help={__(
            "Adds a 10 second rewind button to the player.",
            "presto-player"
          )}
          onChange={(value) => {
            updateState({ "fast-forward": value });
          }}
          checked={state["fast-forward"]}
        />
      </BaseControl>
      <BaseControl className="presto-player__control--progress">
        <ToggleControl
          label={__("Progress Bar", "presto-player")}
          help={__("Shows a seekable progress bar.", "presto-player")}
          onChange={(progress) => {
            updateState({ progress });
          }}
          checked={state.progress}
        />
      </BaseControl>
      <BaseControl className="presto-player__control--current-time">
        <ToggleControl
          label={__("Current Time", "presto-player")}
          help={__("Shows the video timestamp.", "presto-player")}
          onChange={(value) => {
            updateState({ "current-time": value });
          }}
          checked={state["current-time"]}
        />
      </BaseControl>
      <BaseControl className="presto-player__control--volume">
        <ToggleControl
          label={__("Volume", "presto-player")}
          help={__("Shows a volume bar.", "presto-player")}
          onChange={(value) => {
            updateState({ volume: value, mute: value });
          }}
          checked={state.volume}
        />
      </BaseControl>
      <BaseControl className="presto-player__control--speed">
        <ToggleControl
          label={__("Speed", "presto-player")}
          help={__("Shows playback speed controls.", "presto-player")}
          onChange={(speed) => {
            updateState({ speed });
          }}
          checked={state.speed}
        />
      </BaseControl>
      <BaseControl className="presto-player__control--pip">
        <ToggleControl
          label={__("Picture In Picture (HTML5 only)", "presto-player")}
          help={__(
            "Allows users to dock the player on their screen and watch when using other app on their computer.",
            "presto-player"
          )}
          onChange={(pip) => {
            updateState({ pip });
          }}
          checked={state.pip}
        />
      </BaseControl>
      <BaseControl className="presto-player__control--fullscreen">
        <ToggleControl
          label={__("Fullscreen", "presto-player")}
          help={__(
            "Adds a button to allow the player to be fullscreen.",
            "presto-player"
          )}
          onChange={(fullscreen) => {
            updateState({ fullscreen });
          }}
          checked={state.fullscreen}
        />
      </BaseControl>
      <BaseControl className="presto-player__control--captions">
        <ToggleControl
          label={__("Captions", "presto-player")}
          help={__("Shows a dedicated caption toggle button.", "presto-player")}
          onChange={(value) => {
            updateState({ captions: value });
          }}
          checked={state["captions"]}
        />
      </BaseControl>
    </div>
  );
}

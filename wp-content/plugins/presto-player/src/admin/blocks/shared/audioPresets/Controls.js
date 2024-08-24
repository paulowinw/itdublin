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
          help={__("Shows the audio timestamp.", "presto-player")}
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
    </div>
  );
}

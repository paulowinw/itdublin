/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const {
  ToggleControl,
  BaseControl,
  HorizontalRule,
  __experimentalAlignmentMatrixControl: AlignmentMatrixControl,
  SelectControl,
} = wp.components;

export default function ({ state, updateState, className }) {
  return (
    <div className={className}>
      <BaseControl>
        <h3>{__("Behavior", "presto-player")}</h3>
      </BaseControl>
      <BaseControl>
        <ToggleControl
          label={__("Save Play Position", "presto-player")}
          help={__(
            "Saves the user's play position so when they come back to the page they can continue the audio from where they left off.",
            "presto-player"
          )}
          onChange={(save_player_position) => {
            updateState({ save_player_position });
          }}
          checked={state.save_player_position}
        />
      </BaseControl>
      <BaseControl>
        <ToggleControl
          label={__("Show Time Elapsed", "presto-player")}
          help={__(
            "Show the time elapsed or the time remaining for the audio on the player. By default, the time remaining is shown.",
            "presto-player"
          )}
          onChange={(show_time_elapsed) => {
            updateState({ show_time_elapsed });
          }}
          checked={state.show_time_elapsed}
        />
      </BaseControl>
      <BaseControl>
        <ToggleControl
          label={__("Focus Mode", "presto-player")}
          help={__(
            "Play only when tab is visible and audio is in viewport.",
            "presto-player"
          )}
          onChange={(play_video_viewport) => {
            updateState({ play_video_viewport });
          }}
          checked={state.play_video_viewport}
        />
      </BaseControl>

      <BaseControl>
        <ToggleControl
          label={__("Sticky On Scroll", "presto-player")}
          help={__(
            "Stick audios to the side of the screen when the page is scrolled and the audio is playing.",
            "presto-player"
          )}
          onChange={(sticky_scroll) => {
            updateState({ sticky_scroll });
          }}
          checked={state.sticky_scroll}
        />
      </BaseControl>

      <BaseControl>
        <SelectControl
          label={__("On Audio End", "presto-player")}
          value={state.on_video_end}
          options={[
            {
              value: "select",
              label: __("Select", "presto-player"),
            },
            {
              value: "loop",
              label: __("Loop", "presto-player"),
            },
            {
              value: "go-to-start",
              label: __("Go to start", "presto-player"),
            },
          ]}
          onChange={(on_video_end) => {
            updateState({ on_video_end });
          }}
        />
      </BaseControl>
    </div>
  );
}

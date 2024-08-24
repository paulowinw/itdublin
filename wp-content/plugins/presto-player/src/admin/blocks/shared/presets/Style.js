/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { ToggleControl, BaseControl, RangeControl, SelectControl, ColorPicker } =
  wp.components;
import { css, jsx } from "@emotion/core";

export default function ({ state, updateState, className }) {
  return (
    <div className={className}>
      <BaseControl>
        <h3>{__("Style", "presto-player")}</h3>
      </BaseControl>
      <BaseControl>
        <ToggleControl
          label={__("Hide Logo", "presto-player")}
          help={__("Hides the logo on this video.", "presto-player")}
          onChange={(hide_logo) => {
            updateState({ hide_logo });
          }}
          checked={state.hide_logo}
        />
      </BaseControl>
      <BaseControl>
        <RangeControl
          label={__("Round Corners", "presto-player")}
          help={__("Player border radius size.", "presto-player")}
          value={state?.border_radius || 0}
          onChange={(border_radius) => updateState({ border_radius })}
          min={0}
          max={25}
          css={css`
            padding-left: 4px;
            .components-range-control__root {
              align-items: flex-start;
            }
          `}
        />
      </BaseControl>

      <BaseControl>
        <SelectControl
          label={__("Caption Style", "presto-player")}
          labelPosition="top"
          value={state?.caption_style}
          options={[
            { label: __("Default", "presto-player"), value: "default" },
            { label: __("Full", "presto-player"), value: "full" },
          ]}
          onChange={(caption_style) => {
            updateState({ caption_style });
          }}
        />
      </BaseControl>
      <BaseControl>
        <BaseControl.VisualLabel>
          <p>{__("Caption Background", "presto-player")}</p>
        </BaseControl.VisualLabel>

        <ColorPicker
          color={state?.caption_background || "#000000"}
          onChangeComplete={(value) => {
            updateState({ caption_background: value.hex });
          }}
          disableAlpha
        />
      </BaseControl>
    </div>
  );
}

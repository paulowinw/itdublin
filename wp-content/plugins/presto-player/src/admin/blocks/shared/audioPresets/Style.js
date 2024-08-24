/**
 * WordPress dependencies
 */
import { __ } from "@wordpress/i18n";
import {
  ToggleControl,
  BaseControl,
  RangeControl,
  Flex,
} from "@wordpress/components";
import ColorPopup from "../components/ColorPopup";
import { css, jsx } from "@emotion/core";

export default function ({ state, updateState, className }) {
  return (
    <div className={className}>
      <BaseControl>
        <h3>{__("Style", "presto-player")}</h3>
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
        <Flex>
          <BaseControl.VisualLabel>
            <p>{__("Background Color", "presto-player")}</p>
          </BaseControl.VisualLabel>

          <ColorPopup
            color={state?.background_color || "#000000"}
            setColor={(value) => {
              updateState({ background_color: value.hex });
            }}
          />
        </Flex>
      </BaseControl>
      <BaseControl>
        <Flex>
          <BaseControl.VisualLabel>
            <p>{__("Control Color", "presto-player")}</p>
          </BaseControl.VisualLabel>

          <ColorPopup
            color={state?.control_color || "#000000"}
            setColor={(value) => {
              updateState({ control_color: value.hex });
            }}
          />
        </Flex>
      </BaseControl>
    </div>
  );
}

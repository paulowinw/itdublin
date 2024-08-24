/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const {
  ToggleControl,
  BaseControl,
  RadioControl,
  Flex,
  RangeControl,
} = wp.components;

const { useEffect } = wp.element;

import DynamicText from "../overlays/components/DynamicText";

import ColorPopup from "../components/ColorPopup";

export default ({ state, updateState, className }) => {
  const { watermark } = state;

  const defaults = {
    text: __("Enter your watermark text.", "presto-player"),
    position: "top-right",
    color: "#fff",
    backgroundColor: "#333",
    opacity: 80,
  };
  useEffect(() => {
    Object.keys(defaults).forEach((key) => {
      if (state?.watermark?.[key] === undefined) {
        updateWatermarkState({
          [key]: defaults[key],
        });
      }
    });
  }, [state]);

  const updateWatermarkState = (updated) => {
    updateState({
      ...state,
      watermark: {
        ...watermark,
        ...updated,
      },
    });
  };

  return (
    <div className={className}>
      <BaseControl>
        <h3>{__("Dynamic Watermark Text", "presto-player")}</h3>
      </BaseControl>
      <BaseControl className="presto-player__control--watermark">
        <ToggleControl
          label={__("Enable", "presto-player")}
          help={__(
            "Add a simulated dynamic watermark over your video.",
            "presto-player"
          )}
          onChange={(enabled) => {
            updateWatermarkState({
              enabled,
            });
          }}
          checked={watermark?.enabled}
        />
      </BaseControl>

      {watermark?.enabled && (
        <div>
          <DynamicText
            text={watermark?.text}
            update={({ text }) => {
              updateWatermarkState({
                text,
              });
            }}
          />

          <BaseControl className={className}>
            <RadioControl
              label={__("Position", "presto-player")}
              options={[
                { label: __("Top Right", "presto-player"), value: "top-right" },
                { label: __("Top Left", "presto-player"), value: "top-left" },
                {
                  label: __("Change Every 10 Seconds", "presto-player"),
                  value: "randomize",
                },
              ]}
              selected={watermark?.position || "top-right"}
              onChange={(position) =>
                updateWatermarkState({
                  position,
                })
              }
            />
          </BaseControl>

          <BaseControl className="presto-player__control-text-color">
            <Flex>
              <BaseControl.VisualLabel>
                {__("Text Color", "presto-player")}
              </BaseControl.VisualLabel>
              <ColorPopup
                color={watermark?.color || "#fff"}
                setColor={(value) =>
                  updateWatermarkState({
                    color: value && value.hex,
                  })
                }
              />
            </Flex>
          </BaseControl>

          <BaseControl className="presto-player__control-text-color">
            <Flex>
              <BaseControl.VisualLabel>
                {__("Background Color", "presto-player")}
              </BaseControl.VisualLabel>
              <ColorPopup
                color={watermark?.backgroundColor || "#333"}
                setColor={(value) =>
                  updateWatermarkState({
                    backgroundColor: value && value.hex,
                  })
                }
              />
            </Flex>
          </BaseControl>

          <BaseControl>
            <RangeControl
              label={__("Opacity", "presto-player")}
              help={__("Opacity percentage of the watermark.", "presto-player")}
              value={watermark?.opacity || 100}
              onChange={(opacity) => updateWatermarkState({ opacity })}
              min={0}
              max={100}
            />
          </BaseControl>
        </div>
      )}
    </div>
  );
};

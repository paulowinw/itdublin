/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const {
  ToggleControl,
  Flex,
  Button,
  BaseControl,
  RangeControl,
  TextControl,
  SelectControl,
  TextareaControl,
} = wp.components;

const { useEffect, useState } = wp.element;
const { useSelect } = wp.data;

import ColorPopup from "../components/ColorPopup";
import UrlSelect from "../components/UrlSelect";
import YoutubeChannelId from "./parts/YoutubeChannelId";
import { css, jsx } from "@emotion/core";

export default function ({ state, updateState, className, value, setValue }) {
  const { action_bar } = state;
  const [editYoutube, setEditYoutube] = useState(false);

  const branding = useSelect((select) => {
    return select("presto-player/player").branding();
  });
  const youtube = useSelect((select) => {
    return select("presto-player/player").youtube();
  });

  const updateActionBar = (updated) => {
    updateState({
      ...state,
      action_bar: {
        ...action_bar,
        ...updated,
      },
    });
  };

  useEffect(() => {
    if (!action_bar?.text) {
      updateActionBar({
        text: "Like this?",
      });
    }
    if (!action_bar?.button_type) {
      updateActionBar({
        button_type: "custom",
      });
    }

    if (!action_bar?.button_text) {
      updateActionBar({
        button_text: "Click Here",
      });
    }
  }, [state]);

  const renderYoutubeChannelForm = () => {
    if (action_bar?.button_type !== "youtube") {
      return;
    }

    return editYoutube ? (
      <YoutubeChannelId
        onClose={() => setEditYoutube(false)}
        value={value}
        setValue={setValue}
      />
    ) : (
      <div>
        <Button
          isSecondary
          onClick={(e) => {
            e.preventDefault();
            setEditYoutube(true);
          }}
        >
          {youtube?.channel_id
            ? __("Update Youtube Channel Id", "presto-player")
            : __("Add Youtube Channel Id", "presto-player")}
        </Button>
        <br />
        <br />
        <br />
      </div>
    );
  };

  return (
    <div className={className}>
      <BaseControl>
        <h3>{__("Action Bar", "presto-player")}</h3>
      </BaseControl>
      <BaseControl className="presto-player__control--large-play">
        <ToggleControl
          label={__("Enable", "presto-player")}
          help={__(
            "Show an action bar below your player while it's playing.",
            "presto-player"
          )}
          onChange={(enabled) => {
            updateActionBar({
              enabled,
            });
          }}
          checked={action_bar?.enabled}
        />
      </BaseControl>
      {!!action_bar?.enabled && (
        <>
          <BaseControl
            className="presto-player__control--percentage-watched"
            css={css`
              padding-left: 8px;
              margin-bottom: 34px !important;
              .components-range-control__root {
                align-items: flex-start;
              }
            `}
          >
            <RangeControl
              label={__("Display At (Percentage)", "presto-player")}
              labelPosition="top"
              onChange={(percentage_start) => {
                updateActionBar({
                  percentage_start,
                });
              }}
              marks={[
                {
                  value: 0,
                  label: __("Start", "presto-player"),
                },
                {
                  value: 50,
                  label: __("50% Watched", "presto-player"),
                },
                {
                  value: 100,
                  label: __("End", "presto-player"),
                },
              ]}
              shiftStep={5}
              value={action_bar?.percentage_start || 0}
              css={css`
                .components-range-control__slider {
                  position: relative !important;
                }
              `}
            />
          </BaseControl>

          <BaseControl className="presto-player__control--large-play">
            <TextareaControl
              label={__("Text", "presto-player")}
              help={__("Action bar text.", "presto-player")}
              value={action_bar?.text}
              onChange={(text) =>
                updateActionBar({
                  text,
                })
              }
            />
          </BaseControl>

          <BaseControl className="presto-player__control--large-play">
            <Flex>
              <BaseControl.VisualLabel>
                {__("Action Bar Background", "presto-player")}
              </BaseControl.VisualLabel>
              <ColorPopup
                color={action_bar?.background_color || "#1d1d1d"}
                setColor={(value) =>
                  updateActionBar({
                    background_color: value && value.hex,
                  })
                }
              />
            </Flex>
          </BaseControl>

          <BaseControl>
            <h3>{__("Button", "presto-player")}</h3>
          </BaseControl>

          <BaseControl className="presto-player__control--button-type">
            <SelectControl
              label={__("Button Type", "presto-player")}
              value={action_bar?.button_type}
              options={[
                {
                  value: "custom",
                  label: __("Custom", "presto-player"),
                },
                {
                  value: "youtube",
                  label: __("YouTube Subscribe", "presto-player"),
                },
                {
                  value: "none",
                  label: __("None", "presto-player"),
                },
              ]}
              onChange={(button_type) =>
                updateActionBar({
                  button_type,
                })
              }
            />
          </BaseControl>

          {action_bar?.button_type === "youtube" && youtube?.channel_id && (
            <ToggleControl
              label={__("Show Count", "presto-player")}
              help={__("Show your follower count.", "presto-player")}
              onChange={(button_count) => {
                updateActionBar({
                  button_count,
                });
              }}
              checked={action_bar?.button_count}
            />
          )}

          {renderYoutubeChannelForm()}

          {action_bar?.button_type === "custom" && (
            <div>
              <BaseControl className="presto-player__control--button-text">
                <TextControl
                  label={__("Button Text", "presto-player")}
                  help={<p>{__("Submit button text", "presto-player")}</p>}
                  value={action_bar?.button_text}
                  onChange={(button_text) => updateActionBar({ button_text })}
                />
              </BaseControl>
              <BaseControl className="presto-player__control--button-text">
                <BaseControl.VisualLabel>
                  <p> {__("Button Link", "presto-player")}</p>
                </BaseControl.VisualLabel>
                <UrlSelect
                  setSettings={(val) => {
                    updateActionBar({
                      button_link: val,
                    });
                  }}
                  settings={action_bar?.button_link || {}}
                />
              </BaseControl>
              <BaseControl className="presto-player__control--button-radius">
                <RangeControl
                  label={__("Round Corners", "presto-player")}
                  help={__("Border radius of the button", "presto-player")}
                  value={action_bar?.button_radius || 0}
                  onChange={(button_radius) =>
                    updateActionBar({ button_radius })
                  }
                  min={0}
                  max={25}
                />
              </BaseControl>
              <BaseControl className="presto-player__control--button-color">
                <Flex>
                  <BaseControl.VisualLabel>
                    {__("Button Color", "presto-player")}
                  </BaseControl.VisualLabel>
                  <ColorPopup
                    color={action_bar?.button_color || branding?.color}
                    setColor={(value) =>
                      updateActionBar({
                        button_color: value && value.hex,
                      })
                    }
                  />
                </Flex>
              </BaseControl>
              <BaseControl className="presto-player__control--button-text-color">
                <Flex>
                  <BaseControl.VisualLabel>
                    {__("Button Text Color", "presto-player")}
                  </BaseControl.VisualLabel>
                  <ColorPopup
                    color={action_bar?.button_text_color || "#ffffff"}
                    setColor={(value) =>
                      updateActionBar({
                        button_text_color: value && value.hex,
                      })
                    }
                  />
                </Flex>
              </BaseControl>
            </div>
          )}
        </>
      )}
    </div>
  );
}

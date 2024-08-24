/** @jsx jsx */

/**
 * WordPress dependencies
 */
import { __ } from "@wordpress/i18n";
import {
  ToggleControl,
  BaseControl,
  RangeControl,
  TextControl,
  Flex,
  Button,
  TextareaControl,
  Notice,
} from "@wordpress/components";
import { useEffect } from "@wordpress/element";
import { useSelect } from "@wordpress/data";

import UrlSelect from "../components/UrlSelect";
import ColorPopup from "../components/ColorPopup";

import { css, jsx } from "@emotion/core";

function CTA({ state, updateState, className }) {
  const { cta, email_collection } = state;

  const branding = useSelect((select) => {
    return select("presto-player/player").branding();
  });

  // set defaults
  const defaults = {
    percentage: 100,
    show_rewatch: true,
    show_skip: true,
    headline: __("Want to learn more?", "presto-player"),
    show_button: true,
    button_text: __("Click Here", "presto-player"),
    button_link: {
      opensInNewTab: true,
    },
  };
  useEffect(() => {
    Object.keys(defaults).forEach((key) => {
      if (state?.cta?.[key] === undefined) {
        updateCTAState({
          [key]: defaults[key],
        });
      }
    });
  }, [state]);

  const updateCTAState = (updated) => {
    updateState({
      ...state,
      cta: {
        ...cta,
        ...updated,
      },
    });
  };

  const disableEmailCapture = () => {
    updateState({
      ...state,
      email_collection: {
        ...email_collection,
        ...{ enabled: false },
      },
    });
  };

  return (
    <div className={className}>
      <BaseControl>
        <h3>{__("Call To Action", "presto-player")}</h3>
      </BaseControl>
      <BaseControl className="presto-player__control--large-play">
        <ToggleControl
          label={__("Enable", "presto-player")}
          help={__(
            "Show an email collection form and message over your player.",
            "presto-player"
          )}
          onChange={(enabled) => {
            updateCTAState({
              enabled,
            });
          }}
          checked={cta?.enabled}
        />
      </BaseControl>
      {!!cta?.enabled && (
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
              onChange={(percentage) => {
                updateCTAState({
                  percentage,
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
              value={cta?.percentage}
              css={css`
                .components-range-control__slider {
                  position: relative !important;
                }
              `}
            />
          </BaseControl>

          {email_collection?.enabled &&
            email_collection?.percentage === cta?.percentage && (
              <Notice
                css={css`
                  margin: 0 0 30px 0 !important;
                `}
                status="warning"
                isDismissible={false}
              >
                {__(
                  "You already have an email capture set display at the same time.",
                  "presto-player"
                )}
                <Button
                  onClick={disableEmailCapture}
                  isLink
                  css={css`
                    margin-top: 10px !important;
                  `}
                >
                  {__("Disable Email Capture", "presto-player")}
                </Button>
              </Notice>
            )}

          {cta?.percentage === 100 ? (
            <BaseControl className="presto-player__control--show-rewatch">
              <ToggleControl
                label={__("Show Rewatch Button", "presto-player")}
                help={__(
                  "Show a rewatch button at the end of the player.",
                  "presto-player"
                )}
                onChange={(show_rewatch) => {
                  updateCTAState({
                    show_rewatch,
                  });
                }}
                checked={cta?.show_rewatch}
              />
            </BaseControl>
          ) : (
            <BaseControl className="presto-player__control--show-skip">
              <ToggleControl
                label={__("Allow Skipping", "presto-player")}
                help={__(
                  "Let the user continue watching the player.",
                  "presto-player"
                )}
                onChange={(show_skip) => {
                  updateCTAState({
                    show_skip,
                  });
                }}
                checked={cta?.show_skip}
              />
            </BaseControl>
          )}

          <BaseControl className="presto-player__control--button-link">
            <BaseControl.VisualLabel>
              <p> {__("Link", "presto-player")}</p>
            </BaseControl.VisualLabel>
            <UrlSelect
              setSettings={(val) => {
                updateCTAState({
                  button_link: val,
                });
              }}
              settings={cta?.button_link || {}}
            />
          </BaseControl>

          <BaseControl className="presto-player__control--headline">
            <TextareaControl
              label={__("Headline", "presto-player")}
              help={__("The headline for your form.", "presto-player")}
              value={cta?.headline}
              onChange={(headline) => {
                updateCTAState({
                  headline,
                });
              }}
            />
          </BaseControl>

          <BaseControl className="presto-player__control--bottom-text">
            <TextareaControl
              label={__("Bottom Text", "presto-player")}
              help={__(
                "Text displayed below the form. HTML allowed.",
                "presto-player"
              )}
              value={cta?.bottom_text}
              onChange={(bottom_text) => {
                updateCTAState({
                  bottom_text,
                });
              }}
            />
          </BaseControl>

          <BaseControl className="presto-player__control--show-button">
            <ToggleControl
              label={__("Show Button", "presto-player")}
              help={__("Show a call to action button.", "presto-player")}
              onChange={(show_button) => {
                updateCTAState({
                  show_button,
                });
              }}
              checked={cta?.show_button}
            />
          </BaseControl>

          {!!cta?.show_button && (
            <div>
              <BaseControl className="presto-player__control--button-text">
                <TextControl
                  label={__("Button Text", "presto-player")}
                  help={
                    <p>
                      {__(
                        "Button text for the Call To Action",
                        "presto-player"
                      )}
                    </p>
                  }
                  value={cta?.button_text}
                  onChange={(button_text) => updateCTAState({ button_text })}
                />
              </BaseControl>

              <h3>{__("Style", "presto-player")}</h3>

              <BaseControl>
                <RangeControl
                  label={__("Round Corners", "presto-player")}
                  help={__("Border radius of form elements.", "presto-player")}
                  value={cta?.button_radius || 0}
                  onChange={(button_radius) =>
                    updateCTAState({ button_radius })
                  }
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

              <BaseControl className="presto-player__control--button-color">
                <Flex>
                  <BaseControl.VisualLabel>
                    {__("Button Color", "presto-player")}
                  </BaseControl.VisualLabel>
                  <ColorPopup
                    color={cta?.button_color || branding?.color}
                    setColor={(value) =>
                      updateCTAState({
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
                    color={cta?.button_text_color || "#ffffff"}
                    setColor={(value) =>
                      updateCTAState({
                        button_text_color: value && value.hex,
                      })
                    }
                  />
                </Flex>
              </BaseControl>
            </div>
          )}
          <BaseControl>
            <RangeControl
              label={__("Background Opacity", "presto-player")}
              help={__(
                "Opacity percentage of the cover background.",
                "presto-player"
              )}
              value={cta?.background_opacity || 75}
              onChange={(background_opacity) =>
                updateCTAState({ background_opacity })
              }
              min={0}
              max={100}
              css={css`
                padding-left: 4px;
                .components-range-control__root {
                  align-items: flex-start;
                }
              `}
            />
          </BaseControl>
        </>
      )}
    </div>
  );
}

CTA.defaultProps = {
  catName: "Sandy",
  eyeColor: "deepblue",
  age: "120",
};

export default CTA;

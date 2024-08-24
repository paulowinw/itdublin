/** @jsx jsx */
/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { Button, Flex, FlexItem, FlexBlock, Modal } = wp.components;
const { useState, useEffect } = wp.element;
const { useSelect } = wp.data;
import { nanoid } from "nanoid";

import { css, jsx } from "@emotion/core";
import Preview from "../Preview";
import Overlay from "./components/Overlay";

const EditOverlay = ({
  closeModal,
  attributes,
  setAttributes,
  updateOverlayAttribute,
}) => {
  const [loading, setLoading] = useState(false);
  const [currentTime, setCurrentTime] = useState("");
  const { overlays, preset, src, poster, previewSrc } = attributes;

  const branding = useSelect((select) => {
    return select("presto-player/player").branding();
  });

  const state = useSelect((select) =>
    select("presto-player/player").getPreset(preset)
  );

  // maybe add a default so it's not empty.
  useEffect(() => {
    if (!overlays.length) {
      addOverlay();
    }
  }, []);

  // update an existing overlay.
  const updateOverlay = (overlay, data = {}) => {
    let itemIndex = overlays.indexOf(overlay);
    let updated = overlays.map((item, index) => {
      // This isn't the item we care about - keep it as-is
      if (index !== itemIndex) {
        return item;
      }
      // Otherwise, this is the one we want - return an updated value
      return {
        ...item,
        ...data,
      };
    });

    setAttributes({ overlays: updated });
  };

  // remove overlay.
  const removeOverlay = (overlay) => {
    let index = overlays.indexOf(overlay);
    setAttributes({ overlays: overlays.filter((_, i) => i !== index) });
  };

  // add overlay with custom default.
  const addOverlay = () => {
    let defaultOverlay = {
      startTime: "0:00",
      endTime: "0:05",
      text: __("Here's a link to click!", "presto-player"),
      link: {},
      position: "top-right",
      color: "#fff",
      backgroundColor: "#000",
      opacity: 75,
    };

    if (overlays[overlays.length - 1]) {
      const lastOverlay = overlays[overlays.length - 1];
      defaultOverlay = { ...lastOverlay }; // make a shallow clone.
    }

    defaultOverlay.id = nanoid(10);

    setAttributes({
      overlays: [...(overlays || []), ...[defaultOverlay]],
    });
  };

  // sort the overlays by time.
  const sorted = () => {
    return (overlays || []).sort(function (a, b) {
      if (
        parseInt(a.startTime.split(":")[0]) -
          parseInt(b.startTime.split(":")[0]) ===
        0
      ) {
        return (
          parseInt(a.startTime.split(":")[1]) -
          parseInt(b.startTime.split(":")[1])
        );
      } else {
        return (
          parseInt(a.startTime.split(":")[0]) -
          parseInt(b.startTime.split(":")[0])
        );
      }
    });
  };

  // make sure we always update.
  const updateCurrentTimeState = (time) => {
    setCurrentTime("");
    process.nextTick(() => {
      setCurrentTime(time);
    });
  };

  // validate and save
  const save = () => {
    setLoading(true);
    updateOverlayAttribute(overlays);
    setLoading(false);
    closeModal();
  };

  return (
    <Modal
      title={__("Manage Video Overlays", "presto-player")}
      onRequestClose={closeModal}
      className="presto-player__modal-presets"
      overlayClassName="presto-player__modal-presets-overlay"
      shouldCloseOnClickOutside={false}
    >
      <div className="presto-player__preset-options" data-cy="preset-modal">
        <Flex align="stretch" className="presto-player__style-preview-area">
          <FlexItem className="presto-player__style-sidebar">
            <div css={{ padding: "3px" }}>
              {sorted().map((overlay, i) => {
                return (
                  <Overlay
                    key={`${i}-${overlay.startTime}`}
                    overlayIndex={i}
                    className="presto-player__overlay"
                    startTime={overlay.startTime}
                    endTime={overlay.endTime}
                    text={overlay.text}
                    link={overlay.link}
                    position={overlay.position}
                    color={overlay.color}
                    backgroundColor={overlay.backgroundColor}
                    opacity={overlay.opacity}
                    overlay={overlay}
                    update={(data) => {
                      updateOverlay(overlay, data);
                    }}
                    remove={() => {
                      removeOverlay(overlay);
                    }}
                    updateCurrentTime={(data) => {
                      updateCurrentTimeState(data);
                    }}
                  />
                );
              })}
              <Button isPrimary onClick={addOverlay}>
                {__("Add An Overlay", "presto-player")}
              </Button>
            </div>
          </FlexItem>
          <FlexBlock className="presto-player__style-preview-panel">
            <Preview
              preload="auto"
              currentTime={currentTime}
              src={previewSrc || src}
              isDisabled={false}
              state={{
                ...state,
                lazy_load_youtube: false, // don't lazy load.
                invert_time: false,
              }}
              branding={branding}
              poster={poster}
              overlays={overlays}
            />
          </FlexBlock>
        </Flex>

        <br />

        <div
          css={css`
            display: flex;
            align-items: center;
            justify-content: space-between;
          `}
        >
          <div
            css={css`
              opacity: 0.5;
              font-size: 12px;
            `}
          ></div>
          <div>
            <Button isTertiary onClick={closeModal} style={{ margin: "0 6px" }}>
              {__("Cancel", "presto-player")}
            </Button>
            <Button
              isPrimary
              isBusy={loading}
              disabled={loading}
              onClick={save}
              data-cy="submit-preset"
            >
              {__("Save Overlays", "presto-player")}
            </Button>
          </div>
        </div>
      </div>
    </Modal>
  );
};
export default EditOverlay;

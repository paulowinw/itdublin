/** @jsx jsx */
const { __ } = wp.i18n;
const {
  Flex,
  FlexItem,
  TextControl,
  Button,
  BaseControl,
  RadioControl,
  RangeControl,
  withFocusReturn,
} = wp.components;
const { useRef, useEffect } = wp.element;
import { css, jsx } from "@emotion/core";

import { sanitizeTime, timeToSeconds, secondsToTime } from "../../../util";
import UrlSelect from "../../components/UrlSelect";
import DynamicText from "./DynamicText";
import ColorPopup from "../../components/ColorPopup";

const { useState } = wp.element;

const Overlay = ({
  overlayIndex,
  update,
  remove,
  className,
  startTime,
  endTime,
  text,
  link,
  position,
  color,
  backgroundColor,
  opacity,
  updateCurrentTime,
}) => {
  const [draftStartTime, setDraftStartTime] = useState(startTime);
  const [draftEndTime, setDraftEndTime] = useState(endTime);
  const [draftPosition, setDraftPosition] = useState(position);
  const startControl = useRef();

  useEffect(() => {
    if (timeToSeconds(startTime) >= timeToSeconds(endTime)) {
      let endTime = sanitizeTime(startTime);
      let seconds = timeToSeconds(endTime) + 1;
      endTime = sanitizeTime(secondsToTime(seconds));
      update({ endTime });
      setDraftEndTime(endTime);
    }
  }, [startTime, endTime]);

  const updateStartTime = () => {
    const startTime = sanitizeTime(draftStartTime);
    update({ startTime });
    setDraftStartTime(startTime);
    updateCurrentTime(startTime);
  };

  const updateEndTime = () => {
    const endTime = sanitizeTime(draftEndTime);
    update({ endTime });
    setDraftEndTime(endTime);
  };

  return (
    <div>
      <Flex align="center" className={className}>
        <FlexItem>
          <TextControl
            ref={startControl}
            id={`start-time-${overlayIndex}`}
            label={__("Start Time", "presto-player")}
            className="presto-player__overlay--start-time"
            value={draftStartTime}
            onChange={(startTime) => setDraftStartTime(startTime)}
            onBlur={updateStartTime}
            onFocus={updateStartTime}
            autoComplete="off"
            placeholder="0:00"
          />
        </FlexItem>

        <FlexItem>
          <TextControl
            label={__("End Time", "presto-player")}
            className="presto-player__overlay--end-time"
            value={draftEndTime}
            onChange={setDraftEndTime}
            onBlur={updateEndTime}
            autoComplete="off"
            placeholder="0:00"
          />
        </FlexItem>
      </Flex>

      <DynamicText
        text={text}
        update={update}
        onFocus={() => {
          updateCurrentTime(sanitizeTime(draftStartTime));
        }}
      />

      <BaseControl style={{ width: "100%" }}>
        <BaseControl.VisualLabel>
          <p> {__("Link", "presto-player")}</p>
        </BaseControl.VisualLabel>
        <UrlSelect
          onFocus={() => {
            updateCurrentTime(sanitizeTime(draftStartTime));
          }}
          setSettings={(link) => update({ link })}
          settings={link || {}}
        />
      </BaseControl>

      <BaseControl className={className}>
        <RadioControl
          label={__("Position", "presto-player")}
          options={[
            { label: __("Top Right", "presto-player"), value: "top-right" },
            { label: __("Top Left", "presto-player"), value: "top-left" },
          ]}
          selected={draftPosition || "right"}
          onFocus={() => {
            updateCurrentTime(sanitizeTime(draftStartTime));
          }}
          onChange={(position) => {
            update({ position });
            setDraftPosition(position);
            updateCurrentTime(sanitizeTime(draftStartTime));
          }}
        />
      </BaseControl>

      <BaseControl className="presto-player__control--overlay-text-color">
        <Flex>
          <BaseControl.VisualLabel>
            {__("Text Color", "presto-player")}
          </BaseControl.VisualLabel>
          <ColorPopup
            onFocus={() => {
              updateCurrentTime(sanitizeTime(draftStartTime));
            }}
            color={color}
            setColor={(value) => {
              update({
                color: value && value.hex,
              });
            }}
          />
        </Flex>
      </BaseControl>

      <BaseControl className="presto-player__control--overlay-background-color">
        <Flex>
          <BaseControl.VisualLabel>
            {__("Background Color", "presto-player")}
          </BaseControl.VisualLabel>
          <ColorPopup
            onFocus={() => {
              updateCurrentTime(sanitizeTime(draftStartTime));
            }}
            color={backgroundColor}
            setColor={(value) => {
              update({
                backgroundColor: value && value.hex,
              });
            }}
          />
        </Flex>
      </BaseControl>

      <BaseControl>
        <RangeControl
          label={__("Opacity", "presto-player")}
          help={__("Opacity percentage of the overlay.", "presto-player")}
          value={opacity}
          onChange={(opacity) => update({ opacity })}
          min={0}
          max={100}
        />
      </BaseControl>

      {remove && (
        <BaseControl className={className}>
          <Flex justify="flex-end">
            <Button isDestructive isSmall onClick={remove}>
              {__("Remove Overlay", "presto-player")}
            </Button>
          </Flex>
        </BaseControl>
      )}

      <hr css={{ marginBottom: "20px" }} />
    </div>
  );
};

export default Overlay;

import { __ } from "@wordpress/i18n";
import { Flex, FlexItem, FlexBlock, TextControl, Button } from "@wordpress/components";
import { useState } from "@wordpress/element";
import { sanitizeTime } from "../../../util";

const Chapter = ({
  update,
  add,
  remove,
  className,
  time,
  title,
}) => {
  const [draftTime, setDraftTime] = useState(time);

  return (
    <Flex align="center" className={className}>
      <FlexItem>
        <TextControl
          className={"presto-player__caption--time"}
          style={{ width: "60px" }}
          placeholder={"0:00"}
          value={draftTime}
          onChange={(time) => setDraftTime(time)}
          onBlur={() => {
            let time = sanitizeTime(draftTime);
            update({ time });
            setDraftTime(time);
          }}
          autoComplete="off"
        />
      </FlexItem>

      <FlexBlock>
        <TextControl
          className={"presto-player__caption--title"}
          placeholder={__("Title", "presto-player")}
          value={title || ""}
          onChange={(title) => update({ title })}
          autoComplete="off"
        />
      </FlexBlock>

      <FlexItem>
        {remove && (
          <Button
            icon="minus"
            className="ph-chapter__remove"
            style={{ marginBottom: "8px" }}
            label={__("Remove Chapter", "presto-player")}
            onClick={remove}
          />
        )}
        {add && (
          <Button
            icon="plus-alt"
            className="ph-chapter__add"
            label={__("Add Chapter", "presto-player")}
            style={{ marginBottom: "8px" }}
            onClick={() => {
              add();
              setDraftTime("");
            }}
          />
        )}
      </FlexItem>
    </Flex>
  );
};

export default Chapter;

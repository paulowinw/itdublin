import {
  BaseControl,
  Button,
  Flex,
  TextControl,
  ToolbarButton,
} from "@wordpress/components";
import { __ } from "@wordpress/i18n";

export default ({ headers, setHeaders }) => {
  const updateHeaders = (data) => {
    setHeaders([...(headers || []), ...data]);
  };

  const addHeader = (header) => {
    setHeaders([...(headers || []), ...[header]]);
  };
  const updateHeader = (data, index) => {
    setHeaders(
      (headers || []).map((item, i) => {
        if (i !== index) {
          // This isn't the item we care about - keep it as-is
          return item;
        }

        // Otherwise, this is the one we want - return an updated value
        return {
          ...item,
          ...data,
        };
      })
    );
  };

  const removeHeader = (index) =>
    setHeaders((headers || []).filter((_, i) => i !== index));

  return (
    <>
      {(headers || []).map(({ name, value }, index) => {
        return (
          <Flex key={index} align="center">
            <TextControl
              placeholder={__("Header Name", "presto-player")}
              value={name}
              onChange={(name) => updateHeader({ name }, index)}
            />
            <TextControl
              placeholder={__("New Value", "presto-player")}
              value={value}
              onChange={(value) => updateHeader({ value }, index)}
            />
            <BaseControl>
              <ToolbarButton icon="trash" onClick={() => removeHeader(index)} />
            </BaseControl>
          </Flex>
        );
      })}

      <div>
        <Button
          isSecondary
          isSmall
          onClick={() => addHeader({ name: "", value: "" })}
        >
          {__("Add Header", "presto-player")}
        </Button>
      </div>
    </>
  );
};

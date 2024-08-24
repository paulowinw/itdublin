const { __ } = wp.i18n;

import Illustration from "./illustration";
const { Flex, FlexItem, FlexBlock } = wp.components;

export default () => {
  return (
    <Flex style={{ padding: "30px", background: "#fff" }}>
      <FlexItem>
        <Illustration width="250px" />
      </FlexItem>
      <FlexBlock style={{ marginLeft: "20px" }}>
        <h1>{__("Get detailed video insights.", "presto-player")}</h1>
        <p>
          {__(
            "Upgrade to Pro and get video insights like plays, watch-time, and drop off numbers.",
            "presto-player"
          )}
        </p>
        <a
          href="https://prestoplayer.com"
          target="_blank"
          className="button button-primary"
        >
          {__("Learn More", "presto-player")}
        </a>
      </FlexBlock>
    </Flex>
  );
};

/** @jsx jsx */
const { Flex, FlexBlock, Spinner } = wp.components;

import { jsx } from "@emotion/core";

export default ({ className }) => {
  return (
    <Flex className={className}>
      <FlexBlock css={{ textAlign: "center" }}>
        <Spinner />
      </FlexBlock>
    </Flex>
  );
};

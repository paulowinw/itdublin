const { Spinner, Flex, FlexItem } = wp.components;
export default ({ height = 100 }) => {
  return (
    <Flex style={{ height: `${height}px` }} align="center" justify="center">
      <FlexItem>
        <Spinner />
      </FlexItem>
    </Flex>
  );
};

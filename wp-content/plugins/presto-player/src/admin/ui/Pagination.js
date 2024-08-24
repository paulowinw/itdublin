const { __ } = wp.i18n;
const { Card, CardBody, Flex, FlexBlock, Button, ButtonGroup } = wp.components;
const { useState, useEffect } = wp.element;

export default ({ page, setPage, perPage, total, totalPages }) => {
  // do we have prev/next
  const [hasPrevious, setHasPrevious] = useState(false);
  const [hasNext, setHasNext] = useState(false);

  // end and start cursors
  const [end, setEnd] = useState(0);
  const [start, setStart] = useState(0);

  // set end and start
  useEffect(() => {
    setEnd(Math.min(perPage * page, total));
    setStart(perPage * (page - 1) + 1);
  }, [perPage, page, total]);

  // update page when pagination is clicked
  const nextPage = () => {
    setPage(Math.min(totalPages, page + 1));
  };
  const prevPage = () => {
    setPage(Math.max(page - 1, 0));
  };

  // set prev/next
  useEffect(() => {
    setHasPrevious(page - 1 > 0);
    setHasNext(totalPages >= page + 1);
  }, [page, totalPages]);

  return (
    <Card size="large" className="presto-card pagination">
      <CardBody className="presto-flow">
        <Flex>
          <FlexBlock>
            {sprintf(
              __("Showing %1s to %2s of %3s", "presto-player"),
              start,
              end,
              total
            )}
          </FlexBlock>
          <FlexBlock>
            <Flex justify="flex-end">
              {
                <ButtonGroup>
                  <Button
                    isSecondary
                    disabled={!hasPrevious}
                    onClick={prevPage}
                  >
                    {__("Previous", "presto-player")}
                  </Button>
                  <Button isSecondary disabled={!hasNext} onClick={nextPage}>
                    {__("Next", "presto-player")}
                  </Button>
                </ButtonGroup>
              }
            </Flex>
          </FlexBlock>
        </Flex>
      </CardBody>
    </Card>
  );
};

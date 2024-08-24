/** @jsx jsx */
const { __ } = wp.i18n;
const { Flex, FlexBlock, FlexItem, Button, FormFileUpload } = wp.components;
const { dispatch } = wp.data;
import CreateCollection from "./collections/CreateCollection";

import { jsx } from "@emotion/core";

export default ({ afterUpload }) => {
  return (
    <Flex>
      <FlexBlock>
        <Flex justify="flex-start">
          <FormFileUpload
            multiple
            isPrimary
            accept="video/mp4,video/x-m4v,video/*"
            onChange={(e) => {
              if (!e.target.files) {
                return;
              }
              dispatch("presto-player/bunny-popup").addUploads(e.target.files);
              jQuery(e.target).val(null);
            }}
          >
            {__("Upload Videos", "presto-player")}
          </FormFileUpload>{" "}
          <CreateCollection />
          {!!afterUpload && afterUpload}
        </Flex>
      </FlexBlock>
      <FlexItem>
        {/* <Flex align={"stretch"}>
          <input
            class="components-text-control__input"
            type="text"
            placeholder={__("Search for a video...", "presto-player")}
            value={search}
            onChange={(event) => {
              setSearch(event.target.value);
            }}
          />
          <Button
            isPrimary
            size="small"
            onClick={(e) => {
              e.preventDefault();
              onSearch && onSearch(search);
            }}
          >
            {__("Search", "presto-player")}
          </Button>
        </Flex> */}
      </FlexItem>
    </Flex>
  );
};

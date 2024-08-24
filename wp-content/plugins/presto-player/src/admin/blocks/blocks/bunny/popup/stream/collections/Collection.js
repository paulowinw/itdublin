/** @jsx jsx */

const { Icon } = wp.components;
const { dispatch } = wp.data;

import Thumb from "../ThumbTemplate";

import { jsx, css } from "@emotion/core";

export default ({ collection }) => {
  // handle click
  const handleClick = (e) => {
    e.preventDefault();
    dispatch("presto-player/bunny-popup").setCollectionRequest(collection);
    dispatch("presto-player/bunny-popup").setVideosFetched(false);
  };

  return (
    <Thumb
      onClick={handleClick}
      title={
        <div>
          <Icon
            icon="open-folder"
            css={css`
              color: var(--wp-admin-theme-color);
              margin-right: 10px;
            `}
          />
          {collection.name}
        </div>
      }
      footer={<div>{collection.videoCount} Videos</div>}
    />
  );
};

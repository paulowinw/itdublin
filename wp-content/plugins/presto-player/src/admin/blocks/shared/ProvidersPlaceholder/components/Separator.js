import {
  Flex
} from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { css } from "@emotion/core";

const Separator = ({ icon }) => {
  return (
    <Flex
      align="center"
      css={css`
        max-width: 100%;
      `}
    >
      <span
        css={css`
          display: flex;
          max-width: 210px;
        `}
      >
        {icon}
      </span>
      <span>{__('or', 'presto-player')}</span>
      <span
        css={css`
          display: flex;
          max-width: 210px;
        `}
      >
        {icon}
      </span>
    </Flex>
  );
};

export default Separator;

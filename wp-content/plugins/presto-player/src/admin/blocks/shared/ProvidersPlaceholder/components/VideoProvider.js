import {
  Flex
} from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { css } from "@emotion/core";
import ProBadge from "../../components/ProBadge";

const VideoProvider = ({ provider, icon, onCreate, pro }) => {
  return (
    <Flex direction="column" gap="14px" onClick={onCreate}>
      <Flex
        css={css`
          width: 80px;
          min-width: 80px;
          height: 80px;
          border: 1px solid #dddddd;
          border-radius: 4px;
          position: relative;
          &:hover {
            cursor: pointer;
            border-color: #007cba;
            box-shadow: 0px 5px 9px 0px #00000012;
          }
        `}
        justify="center"
        align="center"
      >
        {pro && (
          <div
            css={css`
              position: absolute;
              top: 0;
              right: 0;
              .presto-player__pro-badge {
                margin: 4px;
              }
            `}
          >
            <ProBadge />
          </div>
        )}
        {icon}
      </Flex>
      <Flex
        justify="center"
        css={css`
          height: 20px;
        `}
      >
        <p
          css={css`
            font-weight: 500;
            font-size: 14px;
            margin: 0px !important;
          `}
        >
          {provider}
        </p>
      </Flex>
    </Flex>
  );
};

export default VideoProvider;

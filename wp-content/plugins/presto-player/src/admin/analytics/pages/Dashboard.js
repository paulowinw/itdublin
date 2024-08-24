/** @jsx jsx */

const { __ } = wp.i18n;
const { Flex, FlexBlock, FlexItem } = wp.components;
const { useState, useEffect } = wp.element;

import TopUsers from "../components/TopUsers";
import TopVideos from "../components/TopVideos";
import OverviewPanel from "../components/OverviewPanel";
import DatePicker from "../components/DatePicker";
import apiFetch from "@wordpress/api-fetch";
import { Notice } from "@wordpress/components";
import { css, jsx } from "@emotion/core";

export default function ({ startDate, endDate, setStartDate, setEndDate }) {
  const [noticeStatus, setNoticeStatus] = useState(false);

  // run this only on mount.
  useEffect(() => {
    apiFetch({ path: "/wp/v2/settings" }).then((post) => {
      if (post?.presto_player_analytics?.enable === false) {
        setNoticeStatus(true);
      }
    });
  }, []);

  return (
    <>
      {/* Component decleared below this code  */}
      {noticeStatus ? <MyNotice /> : ""}
      <Flex>
        <FlexBlock>
          <h1>{__("Analytics", "presto-player")}</h1>
        </FlexBlock>
        <FlexItem>
          <DatePicker
            startDate={startDate}
            setStartDate={setStartDate}
            endDate={endDate}
            setEndDate={setEndDate}
          />
        </FlexItem>
      </Flex>

      <div className="presto-flow">
        <div className="presto-dashboard">
          <div className="presto-dashboard__row">
            <div className="presto-dashboard__item is-large">
              <OverviewPanel startDate={startDate} endDate={endDate} />
            </div>
            <div className="presto-dashboard__item">
              <TopUsers startDate={startDate} endDate={endDate} />
            </div>
          </div>

          <div className="presto-dashboard__row">
            <div className="presto-dashboard__item is-large">
              <TopVideos startDate={startDate} endDate={endDate} />
            </div>
          </div>
        </div>
      </div>
    </>
  );
}

const MyNotice = () => (
  <Notice
    css={css`
      margin: 0 0 1em 0 !important;
    `}
    status="warning"
    isDismissible={false}
  >
    <p>
      {__(
        "Analytics are currently disabled. To collect analytics, turn them on in your settings page.",
        "presto-player"
      )}
    </p>
  </Notice>
);

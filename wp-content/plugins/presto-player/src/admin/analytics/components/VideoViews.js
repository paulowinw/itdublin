const { __ } = wp.i18n;
const { compose } = wp.compose;
const { useEffect } = wp.element;

import StatCard from "@/admin/ui/StatCard";
import withStat from "../hocs/withStat";
import { convertDateTimeToAbsoluteDate } from "../util";

export default compose([withStat()])((props) => {
  const { video_id, startDate, endDate, stat, fetchData, loading } = props;

  // fetch data when page changes
  useEffect(() => {
    fetchData({
      endpoint: `/presto-player/v1/analytics/video/${video_id}/views`,
      params: {
        start: convertDateTimeToAbsoluteDate(startDate),
        end: convertDateTimeToAbsoluteDate(endDate),
      },
    });
  }, [startDate, endDate]);

  return (
    <StatCard
      loading={loading}
      value={parseInt(stat)}
      title={__("Unique Views", "presto-player")}
    />
  );
});

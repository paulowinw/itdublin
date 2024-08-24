const { __ } = wp.i18n;
const { compose } = wp.compose;
const { useEffect } = wp.element;

import StatCard from "@/admin/ui/StatCard";
import withStat from "../hocs/withStat";
import { convertDateTimeToAbsoluteDate } from "../util";

export default compose([withStat()])((props) => {
  const { userId, startDate, endDate, stat, fetchData, loading } = props;

  useEffect(() => {
    fetchData({
      endpoint: `/presto-player/v1/analytics/user/${userId}/total-views`,
      params: {
        start: convertDateTimeToAbsoluteDate(startDate),
        end: convertDateTimeToAbsoluteDate(endDate),
      },
    });
  }, [startDate, endDate]);

  return (
    <StatCard
      loading={loading}
      value={parseInt(stat?.view)}
      title={__("Total Views", "presto-player")}
    />
  );
});

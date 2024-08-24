const { __ } = wp.i18n;
const { useEffect } = wp.element;
const { compose } = wp.compose;

import { history } from "@/router/context";
import withDataList from "../hocs/withDataList";
import { convertDateTimeToAbsoluteDate } from "../util";
import DataTable from "./DataTable";

export default compose([withDataList()])(
  ({
    loading,
    page,
    setPage,
    total,
    totalPages,
    data,
    error,
    fetchData,
    startDate,
    endDate,
    userId,
  }) => {
    // 10 per page
    const per_page = 10;

    // fetch data when page changes
    useEffect(() => {
      fetchData({
        endpoint: "/presto-player/v1/analytics/top-videos",
        params: {
          per_page,
          ...(startDate
            ? { start: convertDateTimeToAbsoluteDate(startDate) }
            : {}),
          ...(endDate ? { end: convertDateTimeToAbsoluteDate(endDate) } : {}),
          ...(userId ? { user_id: userId } : {}),
        },
      });
    }, [page, startDate, endDate]);

    const navigate = (id) => {
      const { search } = history.location;
      history.push(`${search}#/video/${id}`);
    };

    const columns = [
      {
        key: "name",
        label: __("Name", "presto-player"),
        render(row) {
          return (
            <h3 style={{ marginBottom: 0, wordBreak: "break-all" }}>
              {row.video.title
                ? row.video.title
                : __("Untitled", "presto-player")}
            </h3>
          );
        },
      },
      {
        key: "total_view",
        label: __("Total View", "presto-player"),
        value(row) {
          return row.stats[0].data;
        },
      },
      {
        key: "avg_view_time",
        label: __("Avg View Time", "presto-player"),
        render(row) {
          return <div className="presto-badge">{row.stats[1].data}</div>;
        },
      },
      {
        key: "view_more",
        label: "",
        render(row) {
          return (
            <span
              style={{
                color: "var(--wp-admin-theme-color, #007cba)",
              }}
            >
              {__("View Details", "presto-player")} &rarr;
            </span>
          );
        },
      },
    ];

    if (error) {
      return { error };
    }

    return (
      <DataTable
        title={__("Top Media", "presto-player")}
        perPage={per_page}
        page={page}
        setPage={setPage}
        loading={loading}
        total={total}
        totalPages={totalPages}
        columns={columns}
        data={data}
        onSelect={(row) => {
          navigate(row?.video?.id);
        }}
      />
    );
  }
);

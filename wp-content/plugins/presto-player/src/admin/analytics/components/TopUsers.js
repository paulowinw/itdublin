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
  }) => {
    // 10 per page
    const per_page = 5;

    // fetch data when page changes
    useEffect(() => {
      fetchData({
        endpoint: "/presto-player/v1/analytics/top-users",
        params: {
          per_page,
          ...(startDate
            ? { start: convertDateTimeToAbsoluteDate(startDate) }
            : {}),
          ...(endDate ? { end: convertDateTimeToAbsoluteDate(endDate) } : {}),
        },
      });
    }, [page, startDate, endDate]);

    // navigate to user screen here
    const navigate = (id) => {
      const { search } = history.location;
      history.push(`${search}#/user/${id}`);
    };

    const columns = [
      {
        key: "name",
        label: __("Name", "presto-player"),
        value: (row) => row?.user?.name,
      },
      {
        key: "total_view",
        label: __("Total View", "presto-player"),
        value: (row) => row?.stats?.[0]?.data,
      },
      {
        key: "avg_view_time",
        label: __("Avg View Time", "presto-player"),
        render: (row) => (
          <div className="presto-badge">{row?.stats?.[1]?.data}</div>
        ),
      },
    ];

    if (error) {
      return { error };
    }

    return (
      <DataTable
        title={__("Top Users", "presto-player")}
        page={page}
        perPage={per_page}
        setPage={setPage}
        loading={loading}
        total={total}
        totalPages={totalPages}
        columns={columns}
        data={data}
        onSelect={(row) => navigate(row?.user?.id)}
      />
    );
  }
);

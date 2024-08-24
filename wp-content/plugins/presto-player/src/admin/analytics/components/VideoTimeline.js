const { __, sprintf } = wp.i18n;
const { Card, CardBody } = wp.components;
const { useState, useEffect, useRef } = wp.element;

import Loading from "@/admin/settings/components/Loading";
import apiFetch from "@/shared/services/fetch";
import Chart from "react-apexcharts";
import { convertDateTimeToAbsoluteDate, timestamp } from "../util";

export default (props) => {
  const { video_id, startDate, endDate } = props;
  const [loading, setLoading] = useState(true);

  const [series, setSeries] = useState([
    {
      name: "Views",
      data: [],
    },
  ]);

  const fetchTimeline = () => {
    setLoading(true);
    apiFetch({
      path: wp.url.addQueryArgs(
        `/presto-player/v1/analytics/video/${video_id}/timeline`,
        {
          start: convertDateTimeToAbsoluteDate(startDate),
          end: convertDateTimeToAbsoluteDate(endDate),
        }
      ),
    })
      .then((data) => {
        let series = [];
        if (data.length) {
          data.forEach((item) => {
            // add another to them
            series.push({
              x: item.watch_time,
              y: item.total,
            });
          });
        }
        setSeries([
          {
            name: "Viewers",
            data: series,
          },
        ]);
      })
      .catch((e) => {
        console.error(e);
      })
      .finally(() => {
        setLoading(false);
      });
  };

  useEffect(() => {
    fetchTimeline();
  }, [startDate, endDate]);

  const chart = {
    options: {
      chart: {
        toolbar: {
          show: false,
        },
      },
      tickAmount: 1,
      yaxis: {
        labels: {
          formatter: function (num) {
            return parseInt(num);
          },
        },
      },
      xaxis: {
        labels: {
          formatter: function (num) {
            return timestamp(num);
          },
        },
      },
      colors: ["#7c3aed"],
      dataLabels: {
        enabled: false,
      },
      stroke: { curve: "smooth" },
      fill: {
        type: "gradient",
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.7,
          opacityTo: 0.9,
          stops: [0, 90, 100],
        },
      },
    },
  };

  if (loading) {
    return (
      <Card>
        <CardBody>
          <Loading />
        </CardBody>
      </Card>
    );
  }

  return (
    <Card>
      <CardBody>
        <div className="presto-card__title">
          {__("Audience Retention", "presto-player")}
        </div>
        <Chart
          options={chart.options}
          series={series}
          type="area"
          height={280}
        />
      </CardBody>
    </Card>
  );
};

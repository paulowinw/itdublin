const { __, sprintf } = wp.i18n;
const { Card, CardBody } = wp.components;
const { useState, useEffect, useRef } = wp.element;

import Loading from "@/admin/settings/components/Loading";
import apiFetch from "@/shared/services/fetch";
import Chart from "react-apexcharts";
import { convertDateTimeToAbsoluteDate, humanSeconds } from "../util";

export default (props) => {
  const [loading, setLoading] = useState(true);
  const [averageTime, setAverageTime] = useState(0);
  const { startDate, endDate } = props;
  const [series, setSeries] = useState([
    {
      name: "Views",
      data: [],
    },
  ]);

  const chart = {
    options: {
      chart: {
        toolbar: {
          show: false,
        },
      },
      yaxis: {
        labels: {
          formatter: function (num) {
            return Math.abs(num) > 999
              ? Math.sign(num) * (Math.abs(num) / 1000).toFixed(1) + "k min"
              : (Math.sign(num) * Math.abs(num)).toFixed(1) + "min";
          },
        },
      },
      colors: ["#7c3aed"],
      xaxis: {
        type: "datetime",
        min: new Date(startDate).setHours(0, 0, 0, 0),
        max: new Date(endDate).setHours(23, 59, 59, 999),
      },
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

  // fetch only if we already mounted
  useEffect(() => {
    fetchMinutes();
  }, [props]);

  const fetchMinutes = () => {
    setLoading(true);
    apiFetch({
      path:
        "/presto-player/v1/analytics/watch-time?" +
        jQuery.param({
          start: convertDateTimeToAbsoluteDate(startDate),
          end: convertDateTimeToAbsoluteDate(endDate),
        }),
      parse: false,
    })
      .then(async (res) => {
        const { data, average } = await res.json();

        setAverageTime(parseFloat(average));

        let series = [];
        if (data.length) {
          data.forEach((item) => {
            series.push({
              x: item.date_time,
              y: (item.total / 60).toFixed(2),
            });
          });
        }
        setSeries([
          {
            name: "Watch Time",
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

  if (loading) {
    return (
      <CardBody>
        <Loading />
      </CardBody>
    );
  }

  return (
    <CardBody className="presto-flow">
      <div className="presto-card__title">
        {sprintf(
          __("%s average watch time", "presto-player"),
          humanSeconds(averageTime)
        )}
      </div>
      <Chart options={chart.options} series={series} type="area" height={280} />
    </CardBody>
  );
};

const { __, sprintf } = wp.i18n;
const { Card, CardBody } = wp.components;
const { useState, useEffect, useRef } = wp.element;

import Loading from "@/admin/settings/components/Loading";
import apiFetch from "@/shared/services/fetch";
import Chart from "react-apexcharts";
import { convertDateTimeToAbsoluteDate } from "../util";

export default (props) => {
  const [loading, setLoading] = useState(true);
  const [totalViews, setTotalViews] = useState(0);
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
        min: 0,
        labels: {
          formatter: function (num) {
            if (num < 1) {
              return 0;
            }
            return Math.abs(num) > 999
              ? Math.sign(num) * (Math.abs(num) / 1000).toFixed(1) + "k"
              : Math.sign(num) * Math.abs(num).toFixed(0);
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
    fetchViews();
  }, [props]);

  const fetchViews = () => {
    setLoading(true);
    apiFetch({
      path:
        "/presto-player/v1/analytics/views?" +
        jQuery.param({
          ...(startDate
            ? { start: convertDateTimeToAbsoluteDate(startDate) }
            : {}),
          ...(endDate ? { end: convertDateTimeToAbsoluteDate(endDate) } : {}),
        }),
      parse: false,
    })
      .then(async (res) => {
        setTotalViews(res.headers && res.headers.get("X-WP-Total"));
        const data = await res.json();

        let series = [];
        if (data.length) {
          data.forEach((item) => {
            series.push({
              x: item.date_time,
              y: item.total,
            });
          });
        }
        setSeries([
          {
            name: "Views",
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
        {sprintf(__("%d Unique Views", "presto-player"), totalViews)}
      </div>
      <Chart options={chart.options} series={series} type="area" height={280} />
    </CardBody>
  );
};

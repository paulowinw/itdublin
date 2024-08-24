const { __ } = wp.i18n;
const {
  Modal,
  Spinner,
  Button,
  BaseControl,
  Flex,
  FlexBlock,
  Notice,
  Card,
  CardBody,
  Disabled,
  DropZone,
  FormFileUpload,
  DropZoneProvider,
} = wp.components;
const { useEffect, useState, useRef, Fragment } = wp.element;

export default () => {
  const bytesToSize = (bytes) => {
    var sizes = ["Bytes", "KB", "MB", "GB", "TB"];
    if (bytes == 0) return "0 Byte";
    var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
    return Math.round(bytes / Math.pow(1024, i), 2) + " " + sizes[i];
  };

  const toDate = (d) => {
    d = new Date(d);
    var hours = d.getHours();
    var minutes = d.getMinutes();
    var ampm = hours >= 12 ? "pm" : "am";
    hours = hours % 12;
    hours = hours ? hours : 12;
    minutes = minutes < 10 ? "0" + minutes : minutes;

    return (
      d.getDate() +
      "-" +
      (d.getMonth() + 1) +
      "-" +
      d.getFullYear() +
      " at " +
      hours +
      ":" +
      minutes +
      ampm
    );
  };
};

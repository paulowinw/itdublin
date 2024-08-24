const { __ } = wp.i18n;
const { useEffect, useState } = wp.element;
const { Card, CardBody } = wp.components;
import Loading from "@/admin/settings/components/Loading";
import Pagination from "@/admin/ui/Pagination";
import Table from "@/admin/ui/Table";

export default ({
  perPage = 10,
  title,
  page,
  setPage,
  loading,
  total,
  totalPages,
  columns,
  data,
  onSelect,
}) => {
  if (loading) {
    return (
      <Card>
        <Loading />
      </Card>
    );
  }

  if (!data?.length) {
    return (
      <Card size="large" className="presto-card">
        <CardBody className="presto-flow">
          <div className="presto-card__title">{title}</div>
          <div style={{ opacity: 0.65 }}>
            {__("No data available.", "presto-player")}
          </div>
        </CardBody>
      </Card>
    );
  }

  return (
    <div className="datatable">
      <Table
        data={data}
        columns={columns}
        perPage={perPage}
        onRowClick={onSelect}
        title={title}
      />

      {!!total && (
        <Pagination
          page={page}
          setPage={setPage}
          perPage={perPage}
          total={total}
          totalPages={totalPages}
        />
      )}
    </div>
  );
};

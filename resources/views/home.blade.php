@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Order List</h2>         
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>OrderId</th>
        <th>Customer Name</th>
        <th>Product</th>
        <th>Price</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody id="orders">
     
    </tbody>
  </table>
</div>
@endsection


@push('scripts')
<script>
    window.axios.get('/api/orders')
    .then((response) => {
        const ordersElement = document.getElementById('orders');
        let orders = response.data;
        

        orders.forEach((order, index) => {

          var tr = document.createElement('tr');
          tr.setAttribute('id', order.id);
          ordersElement.appendChild(tr);
            

          var element = document.createElement('td');
          element.innerText = order.id;
          tr.appendChild(element);

          var element = document.createElement('td');
          element.innerText = order.user.name;
          tr.appendChild(element);

          var element = document.createElement('td');
          element.innerText = order.product.product_name;
          tr.appendChild(element);

          var element = document.createElement('td');
          element.innerText = order.product.price;
          tr.appendChild(element);

          var element = document.createElement('td');
          element.innerText = order.product.status;;
          tr.appendChild(element);

        });
    });
</script>

<script>
    Echo.channel('orders')
    .listen('OrderCreated', (e) => {
        window.axios.get('/api/orders')
        .then((response) => {
            document.getElementById('orders').innerHTML = '';
            const ordersElement = document.getElementById('orders');
            let orders = response.data;
            

            orders.forEach((order, index) => {

              var tr = document.createElement('tr');
              tr.setAttribute('id', order.id);
              ordersElement.appendChild(tr);
                

              var element = document.createElement('td');
              element.innerText = order.id;
              tr.appendChild(element);

              var element = document.createElement('td');
              element.innerText = order.user.name;
              tr.appendChild(element);

              var element = document.createElement('td');
              element.innerText = order.product.product_name;
              tr.appendChild(element);

              var element = document.createElement('td');
              element.innerText = order.product.price;
              tr.appendChild(element);

              var element = document.createElement('td');
              element.innerText = order.product.status;;
              tr.appendChild(element);

            });
        });
  })
</script>
@endpush

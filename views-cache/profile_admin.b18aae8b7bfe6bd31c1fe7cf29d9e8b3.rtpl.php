<?php if(!class_exists('Rain\Tpl')){exit;}?><!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
   Perfil
  </h1>
  <ol class="breadcrumb">
    <li><a href="/admin"><i class="fa fa-dashboard"></i> Home</a></li>
    
  </ol>
</section>

<!-- Main content -->
<section class="content">

  <div class="row">
  	<div class="col-md-12">
  		<div class="box box-success">
        <div class="box-header with-border">
          <h3 class="box-title">Meu Perfil</h3>
        </div>
        <!-- /.box-header -->
            <div class="box-body box-profile">
              <img class="profile-user-img img-responsive img-circle" src="/res/admin/dist/img/user2-160x160.jpg" alt="User profile picture">

              <h3 class="profile-username text-center"><?php echo htmlspecialchars( $user["desperson"], ENT_COMPAT, 'UTF-8', FALSE ); ?></h3>

              <p class="text-muted text-center">Software Engineer</p>

              <ul class="list-group list-group-unbordered">
                <li class="list-group-item">
                  <b>E-mail</b> <a class="pull-right"><?php echo htmlspecialchars( $user["desemail"], ENT_COMPAT, 'UTF-8', FALSE ); ?></a>
                </li>
                <li class="list-group-item">
                  <b>Telefone</b> <a class="pull-right"><?php echo htmlspecialchars( $user["nrphone"], ENT_COMPAT, 'UTF-8', FALSE ); ?></a>
                </li>
                <li class="list-group-item">
                  <b>Nivel de Acesso</b> <a class="pull-right"><?php echo htmlspecialchars( $user["inadmin"], ENT_COMPAT, 'UTF-8', FALSE ); ?></a>
                </li>
              </ul>

              <a href="#" class="btn btn-primary btn-block"><b>Follow</b></a>

            </div>
      
       </div> 
  	</div>
  </div>

</section>
<!-- /.content -->
</div> 
<!-- /.content-wrapper -->
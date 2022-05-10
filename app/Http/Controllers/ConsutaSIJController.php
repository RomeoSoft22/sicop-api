<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsutaSIJController extends Controller
{
    /*******************************************************************************************************
     * DATA FUNCIONAL SISTEMA
     ******************************************************************************************************/

    /****
     * REALIZAR CONSULTA DEL DISTRITO
     */
    public function ConsultaDistrito(){

        
        $list = DB::connection('myOdbcConnection')->select("
        SELECT c_provincia, c_distrito, x_nom_provincia, c_ubigeo 
        FROM provincia 
        WHERE c_distrito=(SELECT cfg_c_distrito FROM ipt_cfg)");     

        return response()->json($list,200);

    }

    /****
     * REALIZAR CONSULTA DE LA SEDE
     */
    public function ConsultaSede(Request $request){

        $consultaSQL = "";

        if($request->eje=='S'){
            $consultaSQL = "
            SELECT distinct s.c_sede, s.x_desc_sede, s.x_direccion, s.latitud_sede, s.longitud_sede 
            FROM cfg_data_digitacion a,
                 instancia b,
                 sede s
           WHERE  a.c_distrito = (SELECT cfg_c_distrito FROM ipt_cfg)
             and a.c_distrito  = b.c_distrito
             and a.c_provincia = b.c_provincia
             and a.c_instancia = b.c_instancia
             AND s.c_sede=b.c_sede
             AND a.n_item='64'"; 

        }else{
            $consultaSQL = "
            SELECT c_sede, x_desc_sede, x_direccion, latitud_sede, longitud_sede 
            FROM sede 
            WHERE c_provincia='$request->c_provincia' AND l_activo='S' AND c_distrito=(SELECT cfg_c_distrito FROM ipt_cfg)"; 
        }

        $list = DB::connection('myOdbcConnection')->select($consultaSQL); 
        

        return response()->json($list,200);

    }

    /****
     * REALIZAR CONSULTA DE LA DEPENDENCIA U ORGANO JURISDICCIONAL
     */
    public function ConsultaDependencia(Request $request){

        $consultaSQL = "";

        if($request->eje=='S'){
            $consultaSQL = "
            SELECT b.c_instancia, b.c_org_jurisd, b.x_nom_instancia, b.x_ubicacion_fisica, b.x_corto 
            FROM cfg_data_digitacion a,
                 instancia b
           WHERE  a.c_distrito = (SELECT cfg_c_distrito FROM ipt_cfg)
             and a.c_distrito  = b.c_distrito
             and a.c_provincia = b.c_provincia
             and a.c_instancia = b.c_instancia
             AND b.c_sede='$request->c_sede'
             AND a.n_item='64'";

        }else{
            $consultaSQL = "
            SELECT c_instancia, c_org_jurisd, x_nom_instancia, x_ubicacion_fisica, x_corto  
            FROM instancia 
            WHERE c_sede='$request->c_sede' AND l_ind_baja='N' AND c_distrito=(SELECT cfg_c_distrito FROM ipt_cfg)";
        }
        $list = DB::connection('myOdbcConnection')->select($consultaSQL); 

        return response()->json($list,200);

    }
    


    /*******************************************************************************************************
     * OTROS
     ******************************************************************************************************/
    /****
     * REALIZAR CONSULTA DEL SIJ - EXPEDIENTE  
     */
    public function ConsultaExpediente($n_expe,$n_anno){

        
        $list = DB::connection('myOdbcConnection')->select("
        SELECT e.x_formato
        FROM expediente e 
        INNER JOIN instancia_expediente ie 
        ON e.n_unico=ie.n_unico AND e.n_incidente=ie.n_incidente AND ie.l_ultimo='S'
        INNER JOIN instancia i 
        ON i.c_instancia=ie.c_instancia
        WHERE ie.n_expediente=".$n_expe." AND ie.n_ano=".$n_anno." AND ie.c_especialidad='CI'
        AND i.c_org_jurisd='03'");     

        return response()->json($list,200);

    }


    /****
     * REALIZAR CONSULTA DEL SIJ - JUEZ
     */
    public function ConsultaUsuario($c_usuario){

        
        $list = DB::connection('myOdbcConnection')->table("usuario")->where('c_usuario',$c_usuario)->get();     

        //return response()->json($list,200);
        return $list;

    }

    /*******************************************************************************************************
     * CONSULTAS DE REPORTE 
     ******************************************************************************************************/

    /****
     * REPORTE LABORAL
     */
    public function ConsultaLaboral(Request $request){
        
        if($request->anio){
            $list['data'] = DB::connection('myOdbcConnection')->select("select     distinct "
            ."codigo = ve.c_instancia, "
            ."sede = s.x_desc_sede, "
            ."instancia = i.x_nom_instancia, "         
            ."eje = sum(case when ie.l_ind_digital = 'S' THEN 1 ELSE 0 END)," 
            ."fisica = sum(case when ie.l_ind_digital in ('N','') THEN 1 ELSE 0 END)," 
            ."total = sum(case when ie.l_ind_digital in ('N','','S') THEN 1 ELSE 0 END) " 
            
            ."from bit_expediente ve, "
            ."acto_procesal_maestro a, "
            ."expediente e, " 
            ."instancia_expediente ie,"  
            ."instancia i, "
            ."sede s "                
            ."where ve.n_unico = e.n_unico and a.c_acto_procesal = ve.c_acto_procesal_hito and ve.c_instancia = i.c_instancia and i.c_sede = s.c_sede and ve.n_unico = ie.n_unico and ve.n_incidente = ie.n_incidente "
            ."and ve.n_instancia_id in ('300188','300189','300190','300191','300201','300185','300186','300187') "
            ."and year(ve.f_registro) = '$request->anio' "
            ."and month(ve.f_registro) = '$request->mes' "
            ."and e.n_incidente   = '0' "
            ."and ie.l_ultimo = 'S' "
            ."and ve.c_acto_procesal_hito in ('018','029','030','034','112','1bl','1bm','227','279','348','349','350','411','420','439','478','486','511','512','514','515','516','522','538','539','545','546','547','567','568','569','570','571','572','573','574','575','576','577','578','579','580','581','582','583','584','588','594','604','605','606','607','608','609','610','611','614','615','622','627','629','630','638','652','654','655','656','657','665','666','667','668','669','670','673','679','693','706','709','710','711','712','766','810','891','928','aaf','aap','aaq','ac3','ad0','ae1','e05','e65','e66','e67','e68','e69','e70','e71','e72','e73','e74','e81','e84','e85','e86','e87','h52','o14','o15','o16','o45','p57','q52','t79','714','715') "
            ."group by   ve.c_instancia, s.x_desc_sede,i.x_nom_instancia "
            ."order by codigo"); 
        }else{
            $list['data'] = '';
        }
            

        return response()->json($list,200);

    }



    /****
     * REPORTE NOTIFICACIONES 
     */
    public function ConsultaNotificar(Request $request){
        
        if($request->c_instancia){
            $list['data'] = DB::connection('myOdbcConnection')->select("
            SELECT usuario = dt.c_usuario, juzgado = i.x_nom_instancia, expediente = e.x_formato, estado = dtt.x_tarea 
            FROM bdt_mov_reg_llegada_bandeja dt, expediente e, instancia i, bdt_mae_tarea dtt 
            WHERE dt.n_unico = e.n_unico 
            AND dt.n_incidente = e.n_incidente
            AND dt.c_instancia = i.c_instancia
            AND dt.c_tarea = dtt.c_tarea
            AND dt.c_distrito =(SELECT cfg_c_distrito FROM ipt_cfg)
            AND i.c_sede ='$request->c_sede'
            AND dt.c_tarea ='0020'
            AND dt.l_ultimo ='S'
            AND dt.c_instancia = '$request->c_instancia'
            ORDER BY 1 "); 
        }else{
            $list['data'] = '';
        }
            

        return response()->json($list,200);

    }


    /****
     * REPORTE FIRMAS 
     */
    public function ConsultaFirmas(Request $request){

        $firma = $request->anio;
        $firma1 = $firma + 1;
        $firma2 = $firma + 2;
        
        if($request->c_sede){
            $list['data'] = DB::connection('myOdbcConnection')->select("
            SELECT sede = s.x_desc_sede, secuencia = ut.n_secuencial, 
            perfil = p.desper,
            usuario = ut.c_usuario , 
            asociacion = convert (varchar,ut.f_asociacion,103),
            vigencia = convert (varchar,ut.f_vigencia,103),
            token = ut.id_token,
            activo = ut.l_activo 
            FROM DBA.usuario_token ut, DBA.usuario u, DBA.perfil p, DBA.sede s
            WHERE u.c_sede = s.c_sede  
            AND ut.c_usuario = u.c_usuario 
            AND u.c_perfil = p.codper 
            AND YEAR (ut.f_asociacion)IN ('$firma1', '$firma') 
            AND ut.l_activo='S' 
            AND YEAR (ut.f_vigencia) in ('$firma1','$firma2') 
            AND s.c_distrito='30' 
            AND u.c_sede='$request->c_sede'
            ORDER BY 1,3 " ); 
        }else{
            $list['data'] = '';
        }
            

        return response()->json($list,200);

    }




    /****
     * REPORTE APELACION 
     */
    public function ConsultaApelacion(Request $request){
        
        if($request->c_instancia && $request->c_especialidad){
            $list['data'] = DB::connection('myOdbcConnection')->select("
            select 	Instancia = ii.x_nom_instancia ,
            Especialidad = e.c_especialidad,
            Expediente = e.x_formato, 
            Registro = convert (varchar,b.f_registro,103), 
            Hito = pm.x_desc_acto_procesal, 
            Dias = DATEDIFF(day,B.F_REGISTRO,getdate()) 
            from 	bit_expediente b, 
            expediente e, 
            acto_procesal_maestro pm,
            instancia ii, 
            instancia_expediente ie 
            where 	b.c_acto_procesal_hito in ('984','0CY') 
            and year(b.f_registro)>2015 
            and b.l_estado='S'  
            and b.n_unico=e.n_unico 
            and b.n_incidente=e.n_incidente 
            AND B.n_acto_procesal=pm.n_acto_procesal
            AND B.c_instancia=ii.c_instancia 
            AND B.n_unico = ie.n_unico
            AND B.n_incidente = ie.n_incidente
            AND ii.c_org_jurisd = '03'
            AND e.c_especialidad  ='$request->c_especialidad' 
            AND ie.l_ultimo = 'S' 
            AND b.C_INSTANCIA = ie.c_instancia 
            AND b.F_REGISTRO > ie.f_ingreso
            AND e.c_instancia ='$request->c_instancia'
            ORDER BY 6 desc " ); 
        }else{
            $list['data'] = '';
        }
            

        return response()->json($list,200);

    }



    /****
     * REPORTE FALLO 
     */
    public function ConsultaFallo(Request $request){

        $finicio = substr($request->fecha, 0, 10);
        $ffin =  substr($request->fecha, 14, 24);
        $finicio = $finicio.' '.'00:00:00.000';
        $ffin = $ffin.' '.'23:59:59.000';
        
        if($request->c_instancia && $finicio!=''){
            $list['data'] = DB::connection('myOdbcConnection')->select("
            SELECT FechaPublicacion = convert (varchar,s.f_aud,103), 
            Juzgado = i.x_nom_instancia, 
            FechaSentidoFallo = convert (varchar,s.f_sentidoFallo,103), 
            Expediente = e.x_formato, 
            EstadoPublicacion = CASE s.l_envio WHEN 'S' THEN 'Publicado en la WEB' ELSE (CASE s.l_exportar WHEN 'S' THEN 'No se Publico en la Web per Se genero Excel' ELSE 'No se Publico en al Web, No se genero Excel' END) END, 
            UsuarioPublico = u1.x_nom_usuario,
            CargoUsuarioPublico = p1.desper 
            FROM MovExpedienteSentidoFallos s 
            JOIN usuario u1 ON u1.c_usuario=s.c_aud_uid 
            JOIN perfil p1 ON p1.codper=u1.c_perfil 
            JOIN expediente e ON e.n_unico = s.n_unico AND e.n_incidente=s.n_incidente
            JOIN instancia i ON i.c_distrito  = s.c_distrito AND i.c_provincia = s.c_provincia AND i.c_instancia = s.c_instancia
            JOIN MovMagistradoSentidoFallos m ON m.n_sentidoFallo=s.n_sentidoFallo
            JOIN usuario u2 ON u2.c_usuario=m.c_usuario
            WHERE s.l_activo='S' 
            AND m.l_activo='S' 
            AND s.c_instancia = '$request->c_instancia' 
            AND s.f_aud BETWEEN '$finicio' and '$ffin'
            ORDER BY FechaPublicacion DESC " ); 
        }else{
            $list['data'] = '';
        }
            

        return response()->json($list,200);

    }



    /****
     * REPORTE ESCRITO CONSOLIDADO 
     */
    public function ConsultaEscritoConsolidado(Request $request){

        if($request->c_provincia){
            $list['data'] = DB::connection('myOdbcConnection')->select("
            SELECT   
            distinct it.c_instancia, ie.c_provincia,	
            INSTANCIA = it.x_nom_instancia, 
            sum(case when month(escrito.f_ingreso_acto) IN (1,2,3,4,5,6,7,8,9,10,11,12) and year(escrito.f_ingreso_acto) BETWEEN ".($request->anio-4)." AND ".($request->anio-1)." and (ISNULL(escrito.l_estado,'@') <> 'A') and escrito.f_respuesta is NULL THEN 1 ELSE 0 END) AS ANTERIOR, 
            sum(case when month(escrito.f_ingreso_acto) = 1 and year(escrito.f_ingreso_acto) = $request->anio and escrito.f_respuesta is NULL THEN 1 ELSE 0 END) AS ENE_P, 
            sum(case when month(escrito.f_respuesta) = 1 and year(escrito.f_respuesta) = $request->anio and escrito.f_respuesta is NOT NULL THEN 1 ELSE 0 END) AS ENE_R, 
            sum(case when month(escrito.f_ingreso_acto) = 2 and year(escrito.f_ingreso_acto) = $request->anio and escrito.f_respuesta is NULL THEN 1 ELSE 0 END) AS FEB_P, 
            sum(case when month(escrito.f_respuesta) = 2 and year(escrito.f_respuesta) = $request->anio and escrito.f_respuesta is NOT NULL THEN 1 ELSE 0 END) AS FEB_R, 
            sum(case when month(escrito.f_ingreso_acto) = 3 and year(escrito.f_ingreso_acto) = $request->anio and escrito.f_respuesta is NULL THEN 1 ELSE 0 END) AS MAR_P, 
            sum(case when month(escrito.f_respuesta) = 3 and year(escrito.f_respuesta) = $request->anio and escrito.f_respuesta is NOT NULL THEN 1 ELSE 0 END) AS MAR_R, 
            sum(case when month(escrito.f_ingreso_acto) = 4 and year(escrito.f_ingreso_acto) = $request->anio and escrito.f_respuesta is NULL THEN 1 ELSE 0 END) AS ABR_P, 
            sum(case when month(escrito.f_respuesta) = 4 and year(escrito.f_respuesta) = $request->anio and escrito.f_respuesta is NOT NULL THEN 1 ELSE 0 END) AS ABR_R, 
            sum(case when month(escrito.f_ingreso_acto) = 5 and year(escrito.f_ingreso_acto) = $request->anio and escrito.f_respuesta is NULL THEN 1 ELSE 0 END) AS MAY_P,  
            sum(case when month(escrito.f_respuesta) = 5 and year(escrito.f_respuesta) = $request->anio and escrito.f_respuesta is NOT NULL THEN 1 ELSE 0 END) AS MAY_R,  
            sum(case when month(escrito.f_ingreso_acto) = 6 and year(escrito.f_ingreso_acto) = $request->anio and escrito.f_respuesta is NULL THEN 1 ELSE 0 END) AS JUN_P,  
            sum(case when month(escrito.f_respuesta) = 6 and year(escrito.f_respuesta) = $request->anio and escrito.f_respuesta is NOT NULL THEN 1 ELSE 0 END) AS JUN_R,  
            sum(case when month(escrito.f_ingreso_acto) = 7 and year(escrito.f_ingreso_acto) = $request->anio and escrito.f_respuesta is NULL THEN 1 ELSE 0 END) AS JUL_P,  
            sum(case when month(escrito.f_respuesta) = 7 and year(escrito.f_respuesta) = $request->anio and escrito.f_respuesta is NOT NULL THEN 1 ELSE 0 END) AS JUL_R,  
            sum(case when month(escrito.f_ingreso_acto) = 8 and year(escrito.f_ingreso_acto) = $request->anio and escrito.f_respuesta is NULL THEN 1 ELSE 0 END) AS AGO_P,  
            sum(case when month(escrito.f_respuesta) = 8 and year(escrito.f_respuesta) = $request->anio and escrito.f_respuesta is NOT NULL THEN 1 ELSE 0 END) AS AGO_R, 
            sum(case when month(escrito.f_ingreso_acto) = 9 and year(escrito.f_ingreso_acto) = $request->anio and escrito.f_respuesta is NULL THEN 1 ELSE 0 END) AS SEP_P,   
            sum(case when month(escrito.f_respuesta) = 9 and year(escrito.f_respuesta) = $request->anio and escrito.f_respuesta is NOT NULL THEN 1 ELSE 0 END) AS SEP_R,  
            sum(case when month(escrito.f_ingreso_acto) = 10 and year(escrito.f_ingreso_acto) = $request->anio and escrito.f_respuesta is NULL THEN 1 ELSE 0 END) AS OCT_P,   
            sum(case when month(escrito.f_respuesta) = 10 and year(escrito.f_respuesta) = $request->anio and escrito.f_respuesta is NOT NULL THEN 1 ELSE 0 END) AS OCT_R,  
            sum(case when month(escrito.f_ingreso_acto) = 11 and year(escrito.f_ingreso_acto) = $request->anio and escrito.f_respuesta is NULL THEN 1 ELSE 0 END) AS NOV_P,  
            sum(case when month(escrito.f_respuesta) = 11 and year(escrito.f_respuesta) = $request->anio and escrito.f_respuesta is NOT NULL THEN 1 ELSE 0 END) AS NOV_R,  
            sum(case when month(escrito.f_ingreso_acto) = 12 and year(escrito.f_ingreso_acto) = $request->anio and escrito.f_respuesta is NULL THEN 1 ELSE 0 END) AS DIC_P, 
            sum(case when month(escrito.f_respuesta) = 12 and year(escrito.f_respuesta) = $request->anio and escrito.f_respuesta is NOT NULL THEN 1 ELSE 0 END) AS DIC_R  
            from escrito  
            ,instancia_expediente as ie  
            ,expediente as ex  
            ,asignado_a as aa  
            ,instancia as it  
            ,acto_procesal as f  
            ,usuario as u  
            where  
            (ISNULL(escrito.l_estado,'@') <> 'A')  
            and(ie.c_distrito = it.c_distrito)  
            and(ie.c_provincia = it.c_provincia)  
            and(ie.c_instancia = it.c_instancia)   
            and(it.c_distrito = (SELECT cfg_c_distrito FROM ipt_cfg)) 
            and(it.c_provincia = '$request->c_provincia')  
            and((it.c_instancia like '%'))  
            and(ISNULL(ex.l_anulado,'N') = 'N')  
            and(ISNULL(ex.l_acumulado,'N') = 'N')  
            and(ie.n_unico = ex.n_unico)  
            and(ie.n_incidente = ex.n_incidente)  
            and(ie.n_unico = escrito.n_unico)  
            and(ie.n_incidente = escrito.n_incidente)  
            and(ie.l_ultimo = 'S')  
            and(ie.l_ultimo_c_org = 'S')  
            and((aa.c_usuario like '%'))  
            and(ie.c_distrito = aa.c_distrito)  
            and(ie.c_provincia = aa.c_provincia)  
            and(ie.c_instancia = aa.c_instancia)  
            and(ie.n_unico = aa.n_unico)  
            and(ie.n_incidente = aa.n_incidente)  
            and(ie.f_ingreso = aa.f_ingreso)  
            and(aa.l_ultimo_instancia = 'S')  
            and(escrito.c_acto_procesal = f.c_acto_procesal)  
            and(u.c_usuario = aa.c_usuario)  
            and(it.l_ind_baja = 'N') 
            GROUP BY   it.c_instancia, ie.c_provincia,it.x_nom_instancia  
            ORDER BY 1 " ); 
        }else{
            $list['data'] = '';
        }
            

        return response()->json($list,200);

    }



    /****
     * REPORTE ESCRITO DETALLE 
     */
    public function ConsultaEscritoDetalle(Request $request){

        $finicio = substr($request->fecha, 0, 10);
        $ffin =  substr($request->fecha, 14, 24);
        $finicio = $finicio.' '.'00:00:00.000';
        $ffin = $ffin.' '.'23:59:59.000';
        
        if($request->c_provincia && $finicio!=''){
            $list['data'] = DB::connection('myOdbcConnection')->select("
            SELECT   distinct it.c_instancia, ie.c_provincia,	
            INSTANCIA = it.x_nom_instancia,
            sum(case when month(escrito.f_ingreso_acto) = 1 THEN 1 ELSE 0 END) AS ENE,
            sum(case when month(escrito.f_ingreso_acto) = 2 THEN 1 ELSE 0 END) AS FEB, 
            sum(case when month(escrito.f_ingreso_acto) = 3 THEN 1 ELSE 0 END) AS MAR, 
            sum(case when month(escrito.f_ingreso_acto) = 4 THEN 1 ELSE 0 END) AS ABR, 
            sum(case when month(escrito.f_ingreso_acto) = 5 THEN 1 ELSE 0 END) AS MAY, 
            sum(case when month(escrito.f_ingreso_acto) = 6 THEN 1 ELSE 0 END) AS JUN, 
            sum(case when month(escrito.f_ingreso_acto) = 7 THEN 1 ELSE 0 END) AS JUL,
            sum(case when month(escrito.f_ingreso_acto) = 8 THEN 1 ELSE 0 END) AS AGO, 
            sum(case when month(escrito.f_ingreso_acto) = 9 THEN 1 ELSE 0 END) AS SEP, 
            sum(case when month(escrito.f_ingreso_acto) = 10 THEN 1 ELSE 0 END) AS OCT, 
            sum(case when month(escrito.f_ingreso_acto) = 11 THEN 1 ELSE 0 END) AS NOV, 
            sum(case when month(escrito.f_ingreso_acto) = 12 THEN 1 ELSE 0 END) AS DIC, 
            sum(case when month(escrito.f_ingreso_acto) IN (1,2,3,4,5,6,7,8,9,10,11,12) THEN 1 ELSE 0 END) AS TOTAL 
            from escrito 
            ,instancia_expediente as ie 
            ,expediente as ex 
            ,asignado_a as aa 
            ,instancia as it 
            ,acto_procesal as f 
            ,usuario as u 
            where(escrito.f_ingreso_acto between '$finicio' and '$ffin') 
            and(escrito.f_respuesta is null) 
            and(ISNULL(escrito.l_estado,'@') <> 'A') 
            and(ie.c_distrito = it.c_distrito) 
            and(ie.c_provincia = it.c_provincia) 
            and(ie.c_instancia = it.c_instancia) 
            and(it.c_distrito = (SELECT cfg_c_distrito FROM ipt_cfg)) 
            and(it.c_provincia = '$request->c_provincia')  
            and((it.c_instancia like '%')) 
            and(ISNULL(ex.l_anulado,'N') = 'N') 
            and(ISNULL(ex.l_acumulado,'N') = 'N')
            and(ie.n_unico = ex.n_unico) 
            and(ie.n_incidente = ex.n_incidente) 
            and(ie.n_unico = escrito.n_unico)
            and(ie.n_incidente = escrito.n_incidente)
            and(ie.l_ultimo = 'S') 
            and(ie.l_ultimo_c_org = 'S')
            and((aa.c_usuario like '%'))
            and(ie.c_distrito = aa.c_distrito) 
            and(ie.c_provincia = aa.c_provincia) 
            and(ie.c_instancia = aa.c_instancia) 
            and(ie.n_unico = aa.n_unico) 
            and(ie.n_incidente = aa.n_incidente)
            and(ie.f_ingreso = aa.f_ingreso)
            and(aa.l_ultimo_instancia = 'S') 
            and(escrito.c_acto_procesal = f.c_acto_procesal) 
            and(u.c_usuario = aa.c_usuario) 
            GROUP BY   it.c_instancia, ie.c_provincia,it.x_nom_instancia
            ORDER BY 1 " ); 
        }else{
            $list['data'] = '';
        }
            

        return response()->json($list,200);

    }



    /****
     * REPORTE ESCRITOS 
     */
    public function ConsultaEscritos(Request $request){

        $finicio = substr($request->fecha, 0, 10);
        $ffin =  substr($request->fecha, 14, 24);
        $finicio = $finicio.' '.'00:00:00.000';
        $ffin = $ffin.' '.'23:59:59.000';
        
        if($request->c_instancia && $finicio!=''){
            $list['data'] = DB::connection('myOdbcConnection')->select("
            DECLARE @pvi_c_distrito char(2)
            DECLARE @pvi_c_provincia char(2)
            DECLARE @pvi_c_instancia char(3)
            DECLARE @pdt_f_ini DATETIME
            DECLARE @pdt_f_fin DATETIME
            DECLARE @pvi_c_usuario varchar(15)
            DECLARE @pvi_c_especialidad char(2)

            SET @pvi_c_distrito = (SELECT cfg_c_distrito FROM ipt_cfg)
            SET @pvi_c_provincia = '$request->c_provincia'
            SET @pvi_c_instancia = '$request->c_instancia'
            SET @pdt_f_ini = '$finicio'
            SET @pdt_f_fin = '$ffin'
            SET @pvi_c_usuario = '%'
            SET @pvi_c_especialidad = '$request->c_especialidad'

            begin
            declare @pvi_c_instancia_es char(3)
            declare @pni_n_unico numeric(20)
            declare @pni_n_incidente integer
            declare @pni_n_sec_ingreso integer
            declare @pni_n_ano_ingreso integer
            declare @pdt_f_ingreso DATETIME
            declare @pni_n_exped integer
            declare @pni_n_ano integer
            declare @c_sede char(4)
            declare @pvi_c_espec char(2)
            declare @pdt_f_ingreso_acto DATETIME
            declare @x_sumilla varchar(700)
            declare @pvi_l_estado char(1)
            declare @x_desc_acto_proc varchar(60)
            declare @c_org char(2)
            declare @x_nom_instancia varchar(60)
            declare @n_unico_relacion numeric(20)
            declare @c_usuario varchar(15)
            declare @x_nom_usuario varchar(50)
            declare @pdt_f_acto_ingreso DATETIME
            declare @pdt_f_respuesta DATETIME
            declare @vi_n_dias integer
            declare @pdt_f_dia DATETIME
            declare @vi_count integer
            declare @dia_semana varchar(30)
            declare @n_act_para_c_usuario integer
            declare @n_act_para_c_inst integer
            declare @formato varchar(50)
            declare @c_instancia_exp char(3) /*@7*/
            declare @c_perfil char(2) /*@7*/
            declare @c_tipo_alerta char(1)
            declare @l_ind_digital char(1) /*@12*/
            create table #esc(
                c_distrito char(2) not null,
                c_provincia char(2) not null,
                c_instancia char(3) not null,
                n_unico numeric(20) not null,
                n_incidente integer not null,
                n_sec_ingreso integer not null,
                n_ano_ingreso integer not null,
                f_ingreso DATETIME null,
                n_expediente integer null,
                n_ano integer null,
                c_sede char(4) null,
                c_especialidad char(2) null,
                x_sumilla varchar(700) null,
                l_estado char(1) null,
                x_desc_acto_procesal varchar(60) null,
                c_org char(2) null,
                x_nom_instancia varchar(60) null,
                n_unico_relacion numeric(20) null,
                c_usuario varchar(15) null,
                x_nom_usuario varchar(50) null,
                f_ingreso_acto DATETIME null,
                f_respuesta DATETIME null,
                n_dias integer null,
                c_instancia_exp char(3) not null, /* x_formato varchar(50) null,*/ /*@7*/
                x_formato varchar(50) null, /*@7*/ --@08
                c_tipo_alerta char(1) null, --@09
                l_ind_digital char(1) null, /*@12*/
                )
            if RTRIM(LTRIM(@pvi_c_usuario)) = '%'
                begin
                set @n_act_para_c_usuario = 0
                end
            else
                begin
                set @n_act_para_c_usuario = 1
                end
            if RTRIM(LTRIM(@pvi_c_instancia)) = '%'
                begin
                set @n_act_para_c_inst = 0
                end
            else
                begin
                set @n_act_para_c_inst = 1
                end
            
            select @c_perfil = c_perfil
                from usuario
                where c_usuario = @pvi_c_usuario

            declare c_escrito dynamic scroll cursor for select ie.c_distrito,
                ie.c_provincia,
                ie.c_instancia,
                ie.n_unico,
                ie.n_incidente,
                escrito.n_sec_ingreso,
                escrito.n_ano_ingreso,
                ie.f_ingreso,
                ie.n_expediente,
                ie.n_ano,
                ie.c_sede,
                ex.c_especialidad,
                escrito.x_sumilla,
                escrito.l_estado,
                f.x_desc_acto_procesal,
                c_org=(case when it.c_org_jurisd = '01' then 'SU'
                when it.c_org_jurisd = '02' then 'SP'
                when it.c_org_jurisd = '03' then 'JR'
                when it.c_org_jurisd = '04' then 'JM'
                when it.c_org_jurisd = '05' then 'JP'
                when it.c_org_jurisd = '06' then 'JN' end),
                it.x_nom_instancia,
                ex.n_unico_relacion,
                aa.c_usuario,
                u.x_nom_usuario,
                escrito.f_ingreso_acto,
                escrito.f_respuesta,
                ex.c_instancia,
                ex.x_formato,
                ex.c_tipo_alerta,
                ie.l_ind_digital
                from escrito
                    ,instancia_expediente as ie
                    ,expediente as ex
                    ,asignado_a as aa
                    ,instancia as it
                    ,acto_procesal as f
                    ,usuario as u
                where(escrito.f_ingreso_acto between @pdt_f_ini and @pdt_f_fin)
                and(escrito.f_respuesta is null)
                and(ISNULL(escrito.l_estado,'@') <> 'A')
                and(ie.c_distrito = it.c_distrito)
                and(ie.c_provincia = it.c_provincia)
                and(ie.c_instancia = it.c_instancia)
                and(it.c_distrito = @pvi_c_distrito)
                and(it.c_provincia = @pvi_c_provincia)
                and((0 = @n_act_para_c_inst) or(it.c_instancia like @pvi_c_instancia))
                and(ISNULL(ex.l_anulado,'N') = 'N')
                and(ISNULL(ex.l_acumulado,'N') = 'N')
                and(ie.n_unico = ex.n_unico)
                and(ie.n_incidente = ex.n_incidente)
                and(ie.n_unico = escrito.n_unico)
                and(ie.n_incidente = escrito.n_incidente)
                and(ie.l_ultimo = 'S')
                and(ie.l_ultimo_c_org = 'S')
                and((0 = @n_act_para_c_usuario) or(aa.c_usuario like @pvi_c_usuario))
                and(ie.c_distrito = aa.c_distrito)
                and(ie.c_provincia = aa.c_provincia)
                and(ie.c_instancia = aa.c_instancia)
                and(ie.n_unico = aa.n_unico)
                and(ie.n_incidente = aa.n_incidente)
                and(ie.f_ingreso = aa.f_ingreso)
                and(aa.l_ultimo_instancia = 'S')
                and(escrito.c_acto_procesal = f.c_acto_procesal)
                and(u.c_usuario = aa.c_usuario)
            open c_escrito
            fetch next c_escrito
                into @pvi_c_distrito,@pvi_c_provincia,@pvi_c_instancia_es,@pni_n_unico,@pni_n_incidente,@pni_n_sec_ingreso,
                @pni_n_ano_ingreso,@pdt_f_ingreso,@pni_n_exped,@pni_n_ano,@c_sede,
                @pvi_c_espec,@x_sumilla,@pvi_l_estado,@x_desc_acto_proc,
                @c_org,@x_nom_instancia,@n_unico_relacion,@c_usuario,@x_nom_usuario,@pdt_f_acto_ingreso,
                @pdt_f_respuesta,
                @c_instancia_exp, /*@07*/
                --@08
                @formato,
                --@09
                @c_tipo_alerta,
                /*@12*/
                @l_ind_digital
            while @@sqlstatus = 0
                begin
                select @vi_n_dias = DATEDIFF(dd,@pdt_f_acto_ingreso,ISNULL(@pdt_f_respuesta,GetDate()))
                select @pdt_f_dia = @pdt_f_acto_ingreso
                select @vi_count = 0
                while @pdt_f_dia <= ISNULL(@pdt_f_respuesta,GetDate())
                    begin
                    select @dia_semana = datename(cdw,@pdt_f_dia)
                    if @dia_semana in( 'Saturday','Sunday','6','7' ) 
                        begin
                        select @vi_count = @vi_count+1
                        end
                    else
                        begin
                        if exists(select 1 from feriados where f_dia_feriado = convert(date,@pdt_f_dia))
                            begin
                            select @vi_count = @vi_count+1
                            end
                        end
                    select @pdt_f_dia = dateadd(dd,1,@pdt_f_dia)
                    end
                select @vi_n_dias = @vi_n_dias-@vi_count
                if @vi_n_dias <= 0
                    begin
                    select @vi_n_dias = 0
                    end
                if @pni_n_sec_ingreso is not null
                    begin
                    insert into #esc
                        ( c_distrito,c_provincia,c_instancia,n_unico,n_incidente,
                        n_sec_ingreso,n_ano_ingreso,f_ingreso,n_expediente,n_ano,
                        c_sede,c_especialidad,x_sumilla,l_estado,x_desc_acto_procesal,
                        c_org,x_nom_instancia,n_unico_relacion,c_usuario,x_nom_usuario,
                        f_ingreso_acto,f_respuesta,n_dias,c_instancia_exp,x_formato,c_tipo_alerta,l_ind_digital )  /*@07*/ --@08 /*@12*/ 
                        values( @pvi_c_distrito,@pvi_c_provincia,@pvi_c_instancia_es,@pni_n_unico,@pni_n_incidente,
                        @pni_n_sec_ingreso,@pni_n_ano_ingreso,@pdt_f_ingreso,@pni_n_exped,@pni_n_ano,
                        @c_sede,@pvi_c_espec,@x_sumilla,@pvi_l_estado,@x_desc_acto_proc,
                        @c_org,@x_nom_instancia,@n_unico_relacion,@c_usuario,@x_nom_usuario,
                        @pdt_f_acto_ingreso,@pdt_f_respuesta,@vi_n_dias,@c_instancia_exp,@formato,isnull(@c_tipo_alerta,'0'),@l_ind_digital )  /*@07*/ --@08 --@09 /*@12*/ 
                    end
                fetch next c_escrito
                    into @pvi_c_distrito,@pvi_c_provincia,@pvi_c_instancia_es,@pni_n_unico,@pni_n_incidente,@pni_n_sec_ingreso,
                    @pni_n_ano_ingreso,@pdt_f_ingreso,@pni_n_exped,@pni_n_ano,@c_sede,
                    @pvi_c_espec,@x_sumilla,@pvi_l_estado,@x_desc_acto_proc,
                    @c_org,@x_nom_instancia,@n_unico_relacion,@c_usuario,@x_nom_usuario,@pdt_f_acto_ingreso,
                    @pdt_f_respuesta,
                    @c_instancia_exp, /*@07*/
                    --@08
                    @formato,
                    --@09
                    @c_tipo_alerta,
                    /*@12*/
                    @l_ind_digital
                end
            close c_escrito
            deallocate cursor c_escrito
            select w.c_distrito,
                w.c_provincia,
                w.c_instancia,
                w.n_unico,
                w.n_incidente,
                w.n_expediente,
                w.n_ano,
                w.c_sede,
                w.c_especialidad,
                w.f_ingreso_acto,
                w.x_sumilla,
                w.l_estado,
                w.x_desc_acto_procesal,
                w.n_sec_ingreso,
                w.n_ano_ingreso,
                w.c_org,
                w.x_nom_instancia,
                w.n_unico_relacion,
                w.c_usuario,
                w.f_ingreso,
                w.x_nom_usuario,
                w.f_respuesta,
                w.n_dias,
                w.c_instancia_exp, /*@07*/
                c_perfil=@c_perfil, /*@07*/
                w.x_formato, --@08
                w.c_tipo_alerta,
                w.l_ind_digital /*@12*/
                from #esc as w
                order by 21 asc
            end
            " ); 
        }else{
            $list['data'] = '';
        }
            

        return response()->json($list,200);

    }



    /****
     * REPORTE INGRESOS 
     */
    public function ConsultaIngreso(Request $request){

        $finicio = substr($request->fecha, 0, 10);
        $ffin =  substr($request->fecha, 14, 24);
        $finicio = $finicio.' '.'00:00:00.000';
        $ffin = $ffin.' '.'23:59:59.000';
        
        if($request->c_provincia && $finicio!=''){
            $list['data'] = DB::connection('myOdbcConnection')->select("
                SELECT DISTINCT e.c_instancia,INSTANCIA = i.x_nom_instancia,
                sum(case when month(e.f_inicio) = 1 THEN 1 ELSE 0 END) AS ENE, 
                sum(case when month(e.f_inicio) = 2 THEN 1 ELSE 0 END) AS FEB, 
                sum(case when month(e.f_inicio) = 3 THEN 1 ELSE 0 END) AS MAR, 
                sum(case when month(e.f_inicio) = 4 THEN 1 ELSE 0 END) AS ABR, 
                sum(case when month(e.f_inicio) = 5 THEN 1 ELSE 0 END) AS MAY, 
                sum(case when month(e.f_inicio) = 6 THEN 1 ELSE 0 END) AS JUN, 
                sum(case when month(e.f_inicio) = 7 THEN 1 ELSE 0 END) AS JUL, 
                sum(case when month(e.f_inicio) = 8 THEN 1 ELSE 0 END) AS AGO, 
                sum(case when month(e.f_inicio) = 9 THEN 1 ELSE 0 END) AS SEP, 
                sum(case when month(e.f_inicio) = 10 THEN 1 ELSE 0 END) AS OCT, 
                sum(case when month(e.f_inicio) = 11 THEN 1 ELSE 0 END) AS NOV, 
                sum(case when month(e.f_inicio) = 12 THEN 1 ELSE 0 END) AS DIC, 
                sum(case when month(e.f_inicio) IN (1,2,3,4,5,6,7,8,9,10,11,12) THEN 1 ELSE 0 END) AS TOTAL 
                FROM expediente e, instancia i, sede s 
                WHERE e.c_instancia = i.c_instancia 
                AND e.c_sede = s.c_sede  
                AND e.f_inicio between '$finicio' and '$ffin' 
                AND e.c_provincia = '$request->c_provincia'
                AND i.c_distrito = (SELECT cfg_c_distrito FROM ipt_cfg)
                AND e.l_anulado ='N' 
                AND e.n_incidente = '0' 
                GROUP BY   e.c_instancia,i.x_nom_instancia 
                ORDER BY 1 " ); 
        }else{
            $list['data'] = '';
        }
            

        return response()->json($list,200);

    }





    /****
     * REPORTE PRODUCCION CONSOLIDADO 
     */
    public function ConsultaProduccionConsolidado(Request $request){

        $finicio = substr($request->fecha, 0, 10);
        $ffin =  substr($request->fecha, 14, 24);
        $finicio = $finicio.' '.'00:00:00.000';
        $ffin = $ffin.' '.'23:59:59.000';
        
        if($request->c_provincia && $finicio!=''){
            $list['data'] = DB::connection('myOdbcConnection')->select("
            SELECT     distinct     SEDE = s.x_desc_sede, 
            INSTANCIA = i.x_nom_instancia, 
            ENE = sum(case when month(VE.f_registro) = 1 THEN 1 ELSE 0 END), 
            FEB = sum(case when month(VE.f_registro) = 2 THEN 1 ELSE 0 END), 
            MAR = sum(case when month(VE.f_registro) = 3 THEN 1 ELSE 0 END), 
            ABR = sum(case when month(VE.f_registro) = 4 THEN 1 ELSE 0 END), 
            MAY = sum(case when month(VE.f_registro) = 5 THEN 1 ELSE 0 END), 
            JUN = sum(case when month(VE.f_registro) = 6 THEN 1 ELSE 0 END), 
            JUL = sum(case when month(VE.f_registro) = 7 THEN 1 ELSE 0 END), 
            AGO = sum(case when month(VE.f_registro) = 8 THEN 1 ELSE 0 END), 
            SEP = sum(case when month(VE.f_registro) = 9 THEN 1 ELSE 0 END), 
            OCT = sum(case when month(VE.f_registro) = 10 THEN 1 ELSE 0 END), 
            NOV = sum(case when month(VE.f_registro) = 11 THEN 1 ELSE 0 END), 
            DIC = sum(case when month(VE.f_registro) = 12 THEN 1 ELSE 0 END), 
            TOTAL = sum(case when month(VE.f_registro) IN (1,2,3,4,5,6,7,8,9,10,11,12) THEN 1 ELSE 0 END)
            FROM            
            BIT_EXPEDIENTE VE, 
            ACTO_PROCESAL_MAESTRO A, 
            EXPEDIENTE E,  
            DBA.instancia i, 
            DBA.sede s 
            WHERE           
            VE.N_UNICO = E.N_UNICO AND VE.n_incidente = E.n_incidente AND A.C_ACTO_PROCESAL = VE.C_ACTO_PROCESAL_HITO AND VE.c_instancia = i.c_instancia AND i.c_sede = s.c_sede 
            AND VE.c_provincia ='$request->c_provincia'
            AND VE.f_registro BETWEEN '$finicio' and '$ffin' 
            AND E.N_INCIDENTE   = '0' 
            AND VE.l_estado ='S' 
            AND s.c_distrito =(SELECT cfg_c_distrito FROM ipt_cfg) 
            AND VE.C_ACTO_PROCESAL_HITO IN ('018','029','030','034','112','1BL','1BM','227','279','348','349','350','411','420','439','478', 
            '486','511','512','514','515','516','522','538','539','545','546','547','567','568','569','570','571','572','573','574','575', 
            '576','577','578','579','580','581','582','583','584','588','594','604','605','606','607','608','609','610','611','614','615', 
            '622','627','629','630','638','652','654','655','656','657','665','666','667','668','669','670','673','679','693','706','709', 
            '710','711','712','766','810','891','928','AAF','AAP','AAQ','AC3','AD0','AE1','E05','E65','E66','E67','E68','E69','E70','E71', 
            'E72','E73','E74','E81','E84','E85','E86','E87','H52','O14','O15','O16','O45','P57','Q52','T79','714','715','716')  
            GROUP BY   s.x_desc_sede,i.x_nom_instancia 
            ORDER BY 1" ); 
        }else{
            $list['data'] = '';
        }
            

        return response()->json($list,200);

    }




    /****
     * REPORTE PRODUCCION DETALLE 
     */
    public function ConsultaProduccionDetalle(Request $request){

        $finicio = substr($request->fecha, 0, 10);
        $ffin =  substr($request->fecha, 14, 24);
        $finicio = $finicio.' '.'00:00:00.000';
        $ffin = $ffin.' '.'23:59:59.000';
        
        if($request->c_provincia && $finicio!=''){
            
            $list['data'] = DB::connection('myOdbcConnection')->select("
            SELECT     distinct     sede = s.x_desc_sede ,
            instancia = i.x_nom_instancia, 
            expediente = e.x_formato, 
            proceso = pm.x_desc_proceso, 
            inicio = convert (varchar,e.f_inicio,103) , 
            admisorio = (SELECT TOP 1 convert (varchar,f_registro,103) FROM DBA.bit_expediente WHERE n_unico=ve.n_unico AND n_incidente= ve.n_incidente AND c_acto_procesal_hito IN ('001','172')), 
            produccion = convert (varchar,ve.f_registro,103), 	
            hito = a.x_desc_acto_procesal  
            FROM            BIT_EXPEDIENTE ve, 
            ACTO_PROCESAL_MAESTRO a, 
            EXPEDIENTE e, 
            instancia i, 
            sede s  ,
            instancia_expediente ie  ,
            proceso_maestro pm 
            WHERE           ve.n_unico = e.n_unico 
            AND ve.n_incidente = e.n_incidente
            AND a.c_acto_procesal = ve.c_acto_procesal_hito 
            AND ve.c_instancia = i.c_instancia 
            AND i.c_sede = s.c_sede 
            AND ve.n_unico = ie.n_unico  
            AND ve.n_incidente = ie.n_incidente 
            AND ie.c_proceso = pm.c_proceso 
            AND ve.c_provincia ='$request->c_provincia' 
            AND s.c_sede='$request->c_sede'
            AND ve.f_registro BETWEEN '$finicio' and '$ffin' 
            AND e.n_incidente   = '0' 
            AND ve.l_estado ='S' 
            AND ve.c_acto_procesal_hito IN ('018','029','030','034','112','1BL','1BM','227','279','348','349','350','411','420','439','478', 
            '486','511','512','514','515','516','522','538','539','545','546','547','567','568','569','570','571','572','573','574','575', 
            '576','577','578','579','580','581','582','583','584','588','594','604','605','606','607','608','609','610','611','614','615', 
            '622','627','629','630','638','652','654','655','656','657','665','666','667','668','669','670','673','679','693','706','709', 
            '710','711','712','766','810','891','928','AAF','AAP','AAQ','AC3','AD0','AE1','E05','E65','E66','E67','E68','E69','E70','E71', 
            'E72','E73','E74','E81','E84','E85','E86','E87','H52','O14','O15','O16','O45','P57','Q52','T79','714','715','716') 
            GROUP BY   s.x_desc_sede,i.x_nom_instancia, e.x_formato,pm.x_desc_proceso,inicio,admisorio,produccion,ve.c_acto_procesal_hito,a.x_desc_acto_procesal 
            ORDER BY 1,2,3 " ); 
        }else{
            $list['data'] = '';
        }
            

        return response()->json($list,200);

    }





     /****
     * REPORTE AUDIENCIA
     */
    public function ConsultaAudiencia(Request $request){

        $finicio = substr($request->fecha, 0, 10);
        $ffin =  substr($request->fecha, 14, 24);
        $finicio = $finicio.' '.'00:00:00.000';
        $ffin = $ffin.' '.'23:59:59.000';
        
        if($request->c_especialidad && $finicio!=''){
            
            $list['data'] = DB::connection('myOdbcConnection')->select("
            select C_SEDE = ie.c_sede, 
            DES_SEDE = s.x_desc_sede, 
            EXPEDIENTE = e.x_formato, 
            ESPECIALIDAD = CASE WHEN e.c_especialidad = 'PE' THEN 'PENAL' 
            WHEN e.c_especialidad = 'LA' THEN 'LABORAL' 
            WHEN e.c_especialidad = 'CI' THEN 'CIVIL' 
            WHEN e.c_especialidad = 'FC' THEN 'FAMILIA CIVIL' 
            WHEN e.c_especialidad = 'FP' THEN 'FAMILIA PENAL'  
            WHEN e.c_especialidad = 'FT' THEN 'FAMILIA TUTELAR' 
            ELSE e.c_especialidad END , 
            a.n_unico, 
            H_INI_R = right(CONVERT(TIME, a.f_ini_audiencia, 108),8), 
            H_FIN_R = right(CONVERT(TIME, a.f_fin_audiencia, 108),8), 
            DURACION = right(CONVERT(TIME, a.f_duracion, 108),8) ,  
            a.n_incidente,
            F_REGISTRO = a.f_inicio,
            H_INI_P = ap.f_ini_prog,
            a.f_termino, 
            H_FIN_P = ap.f_fin_prog, 
            ap.f_fin_real, 
            ap.l_estado, 
            a.f_aud, 
            a.n_programacion,a.c_audiencia,a.n_sala,a.c_instancia, 
            aa.f_duracion,aa.x_motivo,aa.c_usuario_reg,aa.c_estado, 
            ap.n_formato_exp, 
            INSTANCIA = i.x_nom_instancia, 
            SALA = sa.x_descripcion, 
            ta.x_desc_audiencia, 
            DATEFORMAT(getdate(),'YYYY-MM-DD') as F1,
            right(CONVERT(DATE, a.f_inicio),10) as F2,
            ae.x_desc_estado,ae.c_estado_gen, 
            TIPO=ta.x_desc_audiencia, 
            estado_estandar=ae.c_estado_gen, 
            ESTADO =(CASE  WHEN ap.f_audiencia <  convert(date,getdate(),108) AND
                    ap.l_estado = 'PROG' THEN 'NO REALIZADO' 
               WHEN ap.l_estado = 'PROG' THEN 'PROGRAMADO'
               WHEN ap.l_estado = 'REAL' THEN UPPER(ae.x_desc_estado)--'REALIZADO'
               WHEN ap.l_estado = 'PEND' THEN 'NO REALIZADO'
            END) 
            FROM  agenda  a 
            full JOIN audiencia aa ON a.n_programacion=aa.n_programacion AND a.c_audiencia=aa.c_audiencia AND a.n_sala=aa.n_sala 
            inner JOIN audiencia_programacion ap ON a.n_programacion=ap.n_programacion AND a.c_audiencia=ap.c_audiencia AND a.n_sala=ap.n_sala 
            INNER JOIN instancia i ON a.c_instancia=i.c_instancia  
            INNER JOIN instancia_expediente ie ON ie.n_unico=a.n_unico AND ie.n_incidente=a.n_incidente 
            INNER JOIN DBA.expediente e ON e.n_unico=a.n_unico AND e.n_incidente=a.n_incidente 
            LEFT JOIN DBA.sede s ON s.c_sede=ie.c_sede 
            INNER JOIN sala_audiencia sa ON a.n_sala=sa.n_sala 
            INNER JOIN tipo_audiencia ta ON a.c_audiencia=ta.c_audiencia 
            full JOIN audiencia_estado ae ON aa.c_estado=ae.c_estado 
            
            WHERE  
            ie.l_ultimo='S' AND 
            s.l_activo='S' AND 
            e.c_especialidad = '$request->c_especialidad' AND 
            a.l_pendiente  IN ('S','N')  AND 
            a.f_inicio between '$finicio' AND  '$ffin' 
            ORDER BY ie.c_sede ASC, e.c_especialidad DESC, i.x_nom_instancia asc, a.f_inicio ASC" ); 
        }else{
            $list['data'] = '';
        }
            

        return response()->json($list,200);

    }







    /****
     * REPORTE ORIENTACION
     */
    public function ConsultaOrientacion(Request $request){

        $finicio = substr($request->fecha, 0, 10);
        $ffin =  substr($request->fecha, 14, 24);
        $finicio = $finicio.' '.'00:00:00.000';
        $ffin = $ffin.' '.'23:59:59.000';

        if($finicio!=''){

            $list['data'] = DB::connection('ConnectionOrienta')->select("
            SELECT
            fecha = convert (varchar,ua.uafecha,103),
            atencion = ua.uausuario ,
            usuario = ua.uaapellidop+' '+ua.uaapellidom+' '+ua.uanombre ,
            motivo = ua.uamotivo ,
            juridiccional = ua.uaorgano ,
            modalidad = ua.uamodalidad
            FROM tbl_usuario_atendido ua
            WHERE ua.uafecha BETWEEN '$finicio' AND '$ffin'
            ORDER BY 1 " );
        }else{
            $list['data'] = '';
        }


        return response()->json($list,200);

    }




    /****
     * REPORTE INTEROPERABILIDAD
     */
    public function ConsultaInteroperabilidad(Request $request){

        $finicio = substr($request->fecha, 0, 10);
        $ffin =  substr($request->fecha, 14, 24);
        $finicio = $finicio.' '.'00:00:00.000';
        $ffin = $ffin.' '.'23:59:59.000';

        if($request->c_sede && $finicio!=''){

            $list['data'] = DB::connection('myOdbcConnection')->select("
            SELECT  Fecha_ingreso = convert (varchar,ie.f_ingreso,103),
            Sede = s.x_desc_sede,
            Instancia = i.x_nom_instancia,
            Expediente = e.x_formato,
            ComisariaFamilia = e.x_destinatario,
            Horas_Atencion = CAST (DATEDIFF(hour, ie.f_ingreso,h.f_ingreso_acto)AS VARCHAR(5))+' horas',
            //Dias_Atencion = h.f_ingreso_acto - ie.f_ingreso,
            ActoProcesal =(SELECT x_desc_acto_procesal FROM acto_procesal WHERE c_acto_procesal = h.c_acto_procesal),
            Medida = case when (SELECT l_flag_otorgamp FROM  ParteTipoRiesgo WHERE n_unico = ie.n_unico AND n_incidente = ie.n_incidente AND l_ultimo = 'S' AND n_secuencia = 1) = 'S' THEN 'SI' ELSE 'NO' END
            FROM  instancia_expediente ie
            LEFT OUTER JOIN  historia h
            ON h.c_distrito = ie.c_distrito
            AND h.c_provincia = ie.c_provincia
            AND h.c_instancia = ie.c_instancia
            AND h.n_unico = ie.n_unico
            AND h.n_incidente = ie.n_incidente
            AND h.f_ingreso = ie.f_ingreso
            //AND h.l_ultimo = 'S'
            AND h.c_acto_procesal IN ('1BN','332')
            INNER JOIN  instancia i
            ON i.c_distrito = ie.c_distrito
            AND i.c_provincia = ie.c_provincia
            AND i.c_instancia = ie.c_instancia
            INNER JOIN  procedencia_maestro pm
            ON pm.c_procedencia = ie.c_procedencia
            AND pm.l_activo = 'S'
            INNER JOIN  distrito_judicial d
            ON d.c_distrito = ie.c_distrito
            INNER JOIN  sede s
            ON s.c_sede = ie.c_sede
            AND s.l_activo = 'S'
            LEFT OUTER JOIN  expediente_detalle ee
            ON ee.n_unico = ie.n_unico
            AND ee.n_incidente = ie.n_incidente
            AND ee.f_registro  = ie.f_ingreso
            LEFT OUTER JOIN  expediente e
            ON e.c_distrito = ie.c_distrito
            AND e.c_provincia = ie.c_provincia
            AND e.c_instancia = ie.c_instancia
            AND e.n_unico = ie.n_unico
            AND e.n_incidente = ie.n_incidente
            AND e.f_inicio = ie.f_ingreso
            LEFT OUTER JOIN escrito es
            ON es.n_unico = ee.n_unico
            AND es.n_incidente = ee.n_incidente
            AND Len(es.x_resolucion) >= 1
            AND es.l_ultimo = 'S'

            WHERE ie.f_ingreso BETWEEN '$finicio' and '$ffin'
            AND ie.c_especialidad = 'FT'
            AND ee.c_numCii IS NOT NULL
            AND ie.c_provincia ='$request->c_sede'
            AND e.x_destinatario IN (
                'POLICIA NACIONAL DEL PERU - COMISARIA DE FAMILIA - SECCION INVESTIGACIONES - VILLA EL SALVADOR',
                'MINISTERIO DEL INTERIOR - COMISARIA DE LA FAMILIA DE SAN JUAN DE MIRAFLORES',
                'POLICIA NACIONAL DEL PERU - COMISARIA MANCHAY')
                AND s.x_desc_sede <> 'Sede CISAJ - Villa EL Salvador'
                AND pm.x_desc_procedencia = 'POLICIA NACIONAL DEL PERU'
                ORDER BY 2,3,6 " );
        }else{
            $list['data'] = '';
        }


        return response()->json($list,200);

    }




    /****
     * REPORTE MODULO DE VIOLENCIA
     */
    public function ConsultaMV(Request $request){

        $finicio = substr($request->fecha, 0, 10);
        $ffin =  substr($request->fecha, 14, 24);
        $finicio = $finicio.' '.'00:00:00.000';
        $ffin = $ffin.' '.'23:59:59.000';

        if($request->c_distrito && $finicio!=''){

            $list['data'] = DB::connection('ConnectionMV')->select("
            SELECT
            juzgado = mv.hdvfjuzgado,
            expediente = mv.hdvfnexpediente,
            distrito = mv.hdvflhechos,
            fecha = convert (varchar,mv.hdvffechaIN,103)
            FROM DBA.tbl_historial_dvf mv
            WHERE mv.hdvflhechos ='$request->c_distrito'
            AND hdvffechaIN BETWEEN '$finicio' AND '$ffin'" );
        }else{
            $list['data'] = '';
        }


        return response()->json($list,200);

    }
}

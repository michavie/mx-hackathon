import { Module } from '@nestjs/common'
import { ContractService } from './contract.service'
import { AdminService } from 'src/admin/admin.service'
import { ContractController } from './contract.controller'

@Module({
  controllers: [ContractController],
  providers: [ContractService, AdminService],
})
export class ContractModule {}
